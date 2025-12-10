# Comprehensive Testing Guide

This document provides complete testing strategies and examples for the Next.js rewrite.

---

## Table of Contents
1. [Testing Setup](#testing-setup)
2. [Unit Tests](#unit-tests)
3. [Integration Tests](#integration-tests)
4. [E2E Tests](#e2e-tests)
5. [API Tests](#api-tests)
6. [Component Tests](#component-tests)
7. [Test Utilities](#test-utilities)
8. [Coverage Requirements](#coverage-requirements)

---

## Testing Setup

### Test Setup File

**File**: `tests/setup.ts`

```typescript
import { beforeAll, afterEach, afterAll, vi } from "vitest";
import { cleanup } from "@testing-library/react";
import "@testing-library/jest-dom/vitest";

// Mock environment variables
process.env.NODE_ENV = "test";
process.env.DATABASE_URL = "postgresql://postgres:password@localhost:5432/serpsurfer_test";
process.env.NEXTAUTH_SECRET = "test-secret";
process.env.NEXTAUTH_URL = "http://localhost:3000";

// Mock Next.js router
vi.mock("next/navigation", () => ({
  useRouter: () => ({
    push: vi.fn(),
    replace: vi.fn(),
    prefetch: vi.fn(),
    back: vi.fn(),
  }),
  usePathname: () => "/test",
  useSearchParams: () => new URLSearchParams(),
}));

// Mock NextAuth
vi.mock("next-auth", () => ({
  getServerSession: vi.fn(),
}));

// Mock Prisma
vi.mock("@/lib/db", () => ({
  prisma: {
    user: {
      findUnique: vi.fn(),
      findMany: vi.fn(),
      create: vi.fn(),
      update: vi.fn(),
      delete: vi.fn(),
      count: vi.fn(),
    },
    sitemap: {
      findUnique: vi.fn(),
      findMany: vi.fn(),
      create: vi.fn(),
      update: vi.fn(),
      delete: vi.fn(),
      count: vi.fn(),
    },
    urlList: {
      findUnique: vi.fn(),
      findMany: vi.fn(),
      create: vi.fn(),
      update: vi.fn(),
      delete: vi.fn(),
      count: vi.fn(),
    },
    indexQueue: {
      findUnique: vi.fn(),
      findMany: vi.fn(),
      create: vi.fn(),
      update: vi.fn(),
      delete: vi.fn(),
      count: vi.fn(),
    },
  },
}));

// Cleanup after each test
afterEach(() => {
  cleanup();
  vi.clearAllMocks();
});

// Global test setup
beforeAll(() => {
  console.log("Setting up tests...");
});

// Global test teardown
afterAll(() => {
  console.log("Tests completed");
});
```

---

## Unit Tests

### Service Tests

**File**: `tests/unit/services/sitemap.service.test.ts`

```typescript
import { describe, it, expect, beforeEach, vi } from "vitest";
import { SitemapService } from "@/services/sitemap.service";
import { prisma } from "@/lib/db";
import axios from "axios";

vi.mock("axios");
vi.mock("@/lib/queue/client");

describe("SitemapService", () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  describe("fetchSitemap", () => {
    it("should fetch and parse sitemap successfully", async () => {
      const mockXml = `<?xml version="1.0" encoding="UTF-8"?>
        <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
          <url>
            <loc>https://example.com/page1</loc>
            <lastmod>2024-01-01</lastmod>
            <priority>0.8</priority>
          </url>
        </urlset>`;

      (axios.get as any).mockResolvedValue({ data: mockXml });

      const result = await SitemapService.fetchSitemap(
        "https://example.com/sitemap.xml"
      );

      expect(result.success).toBe(true);
      expect(result.data).toBeDefined();
      expect(axios.get).toHaveBeenCalledWith(
        "https://example.com/sitemap.xml",
        expect.objectContaining({
          timeout: 30000,
        })
      );
    });

    it("should throw error on fetch failure", async () => {
      (axios.get as any).mockRejectedValue(new Error("Network error"));

      await expect(
        SitemapService.fetchSitemap("https://example.com/sitemap.xml")
      ).rejects.toThrow("Failed to fetch sitemap");
    });
  });

  describe("parseSitemapUrls", () => {
    it("should parse regular sitemap URLs", () => {
      const mockData = {
        urlset: {
          url: [
            {
              loc: "https://example.com/page1",
              lastmod: "2024-01-01",
              priority: "0.8",
            },
            {
              loc: "https://example.com/page2",
              lastmod: "2024-01-02",
              priority: "0.6",
            },
          ],
        },
      };

      const result = SitemapService.parseSitemapUrls(mockData);

      expect(result.isSitemapIndex).toBe(false);
      expect(result.urls).toHaveLength(2);
      expect(result.urls[0].url).toBe("https://example.com/page1");
      expect(result.urls[0].priority).toBe(0.8);
    });

    it("should identify sitemap index", () => {
      const mockData = {
        sitemapindex: {
          sitemap: [
            {
              loc: "https://example.com/sitemap1.xml",
              lastmod: "2024-01-01",
            },
          ],
        },
      };

      const result = SitemapService.parseSitemapUrls(mockData);

      expect(result.isSitemapIndex).toBe(true);
      expect(result.sitemaps).toHaveLength(1);
    });
  });

  describe("importSitemap", () => {
    it("should create sitemap and queue for processing", async () => {
      const mockUserId = "user-123";
      const mockUrl = "https://example.com/sitemap.xml";

      const mockXml = `<?xml version="1.0" encoding="UTF-8"?>
        <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
          <url><loc>https://example.com/page1</loc></url>
        </urlset>`;

      (axios.get as any).mockResolvedValue({ data: mockXml });
      (prisma.sitemap.findFirst as any).mockResolvedValue(null);
      (prisma.sitemap.create as any).mockResolvedValue({
        id: "sitemap-123",
        userId: mockUserId,
        url: mockUrl,
      });

      const result = await SitemapService.importSitemap(mockUserId, mockUrl);

      expect(result.id).toBe("sitemap-123");
      expect(prisma.sitemap.create).toHaveBeenCalledWith(
        expect.objectContaining({
          data: expect.objectContaining({
            userId: mockUserId,
            url: mockUrl,
          }),
        })
      );
    });

    it("should throw error if sitemap already exists", async () => {
      (prisma.sitemap.findFirst as any).mockResolvedValue({ id: "existing" });

      await expect(
        SitemapService.importSitemap("user-123", "https://example.com/sitemap.xml")
      ).rejects.toThrow("Sitemap already exists");
    });
  });

  describe("getSitemapStats", () => {
    it("should return correct statistics", async () => {
      const mockSitemapId = "sitemap-123";

      (prisma.sitemapUrl.count as any)
        .mockResolvedValueOnce(100) // total
        .mockResolvedValueOnce(70)  // indexed
        .mockResolvedValueOnce(30); // not indexed

      const stats = await SitemapService.getSitemapStats(mockSitemapId);

      expect(stats.totalUrls).toBe(100);
      expect(stats.indexedUrls).toBe(70);
      expect(stats.notIndexedUrls).toBe(30);
      expect(stats.indexingRate).toBe(70);
    });
  });
});
```

### Utility Tests

**File**: `tests/unit/lib/utils.test.ts`

```typescript
import { describe, it, expect } from "vitest";
import { cn, formatDate, truncateUrl } from "@/lib/utils";

describe("Utils", () => {
  describe("cn", () => {
    it("should merge class names correctly", () => {
      const result = cn("text-red-500", "bg-blue-500");
      expect(result).toContain("text-red-500");
      expect(result).toContain("bg-blue-500");
    });

    it("should handle conditional classes", () => {
      const result = cn("base-class", {
        "conditional-class": true,
        "not-included": false,
      });
      expect(result).toContain("base-class");
      expect(result).toContain("conditional-class");
      expect(result).not.toContain("not-included");
    });
  });

  describe("formatDate", () => {
    it("should format date correctly", () => {
      const date = new Date("2024-01-15T10:30:00Z");
      const result = formatDate(date);
      expect(result).toMatch(/Jan|January/);
      expect(result).toContain("15");
      expect(result).toContain("2024");
    });

    it("should handle invalid dates", () => {
      const result = formatDate(null);
      expect(result).toBe("Never");
    });
  });

  describe("truncateUrl", () => {
    it("should truncate long URLs", () => {
      const longUrl = "https://example.com/very/long/path/that/should/be/truncated";
      const result = truncateUrl(longUrl, 30);
      expect(result.length).toBeLessThanOrEqual(33); // 30 + "..."
      expect(result).toContain("...");
    });

    it("should not truncate short URLs", () => {
      const shortUrl = "https://example.com";
      const result = truncateUrl(shortUrl, 50);
      expect(result).toBe(shortUrl);
    });
  });
});
```

---

## Integration Tests

### API Integration Tests

**File**: `tests/integration/api/sitemaps.test.ts`

```typescript
import { describe, it, expect, beforeAll, afterAll } from "vitest";
import { createMocks } from "node-mocks-http";
import { GET, POST } from "@/app/api/sitemaps/route";
import { prisma } from "@/lib/db";

describe("Sitemaps API Integration", () => {
  let testUserId: string;
  let testSitemapId: string;

  beforeAll(async () => {
    // Create test user
    const user = await prisma.user.create({
      data: {
        email: "test@example.com",
        name: "Test User",
        roleId: "test-role-id",
      },
    });
    testUserId = user.id;
  });

  afterAll(async () => {
    // Cleanup
    await prisma.sitemap.deleteMany({
      where: { userId: testUserId },
    });
    await prisma.user.delete({
      where: { id: testUserId },
    });
  });

  describe("GET /api/sitemaps", () => {
    it("should return user sitemaps", async () => {
      // Create test sitemap
      const sitemap = await prisma.sitemap.create({
        data: {
          userId: testUserId,
          url: "https://example.com/sitemap.xml",
        },
      });
      testSitemapId = sitemap.id;

      const { req } = createMocks({
        method: "GET",
        url: "/api/sitemaps",
      });

      // Mock session
      (getServerSession as any).mockResolvedValue({
        user: { id: testUserId },
      });

      const response = await GET(req as any);
      const data = await response.json();

      expect(response.status).toBe(200);
      expect(data.sitemaps).toBeInstanceOf(Array);
      expect(data.sitemaps.length).toBeGreaterThan(0);
    });

    it("should return 401 for unauthenticated requests", async () => {
      const { req } = createMocks({
        method: "GET",
      });

      (getServerSession as any).mockResolvedValue(null);

      const response = await GET(req as any);

      expect(response.status).toBe(401);
    });
  });

  describe("POST /api/sitemaps", () => {
    it("should create new sitemap", async () => {
      const { req } = createMocks({
        method: "POST",
        body: {
          url: "https://example.com/new-sitemap.xml",
          autoScan: true,
        },
      });

      (getServerSession as any).mockResolvedValue({
        user: { id: testUserId },
      });

      const response = await POST(req as any);
      const data = await response.json();

      expect(response.status).toBe(201);
      expect(data.url).toBe("https://example.com/new-sitemap.xml");
      expect(data.autoScan).toBe(true);
    });

    it("should reject invalid URL", async () => {
      const { req } = createMocks({
        method: "POST",
        body: {
          url: "not-a-valid-url",
        },
      });

      (getServerSession as any).mockResolvedValue({
        user: { id: testUserId },
      });

      const response = await POST(req as any);

      expect(response.status).toBe(400);
    });
  });
});
```

---

## E2E Tests

### Playwright E2E Tests

**File**: `tests/e2e/auth.spec.ts`

```typescript
import { test, expect } from "@playwright/test";

test.describe("Authentication", () => {
  test("should show login page", async ({ page }) => {
    await page.goto("/login");
    await expect(page.locator("h1")).toContainText("Sign In");
  });

  test("should login with credentials", async ({ page }) => {
    await page.goto("/login");

    await page.fill('input[name="email"]', "test@example.com");
    await page.fill('input[name="password"]', "TestPassword123");
    await page.click('button[type="submit"]');

    // Should redirect to dashboard
    await expect(page).toHaveURL("/dashboard");
    await expect(page.locator("h1")).toContainText("Dashboard");
  });

  test("should show error for invalid credentials", async ({ page }) => {
    await page.goto("/login");

    await page.fill('input[name="email"]', "test@example.com");
    await page.fill('input[name="password"]', "wrongpassword");
    await page.click('button[type="submit"]');

    await expect(page.locator('[role="alert"]')).toContainText(
      "Invalid credentials"
    );
  });

  test("should register new user", async ({ page }) => {
    await page.goto("/register");

    await page.fill('input[name="name"]', "New User");
    await page.fill('input[name="email"]', `test-${Date.now()}@example.com`);
    await page.fill('input[name="password"]', "NewPassword123");
    await page.click('button[type="submit"]');

    // Should redirect to dashboard
    await expect(page).toHaveURL("/dashboard");
  });
});
```

**File**: `tests/e2e/sitemaps.spec.ts`

```typescript
import { test, expect } from "@playwright/test";

test.describe("Sitemap Management", () => {
  test.beforeEach(async ({ page }) => {
    // Login
    await page.goto("/login");
    await page.fill('input[name="email"]', "test@example.com");
    await page.fill('input[name="password"]', "TestPassword123");
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL("/dashboard");
  });

  test("should display sitemaps list", async ({ page }) => {
    await page.goto("/sitemaps");
    await expect(page.locator("h1")).toContainText("Sitemaps");
    await expect(page.locator("table")).toBeVisible();
  });

  test("should add new sitemap", async ({ page }) => {
    await page.goto("/sitemaps");

    await page.click('button:has-text("Add Sitemap")');
    await page.fill('input[name="url"]', "https://example.com/sitemap.xml");
    await page.click('button[type="submit"]');

    // Should show success message
    await expect(page.locator('[role="alert"]')).toContainText(
      "Sitemap added successfully"
    );

    // Should appear in list
    await expect(page.locator("table")).toContainText(
      "example.com/sitemap.xml"
    );
  });

  test("should toggle auto-scan", async ({ page }) => {
    await page.goto("/sitemaps");

    // Find first sitemap row
    const row = page.locator("table tbody tr").first();

    // Click auto-scan toggle
    const toggle = row.locator('[role="switch"]');
    const initialState = await toggle.getAttribute("aria-checked");

    await toggle.click();

    // Wait for update
    await page.waitForTimeout(500);

    const newState = await toggle.getAttribute("aria-checked");
    expect(newState).not.toBe(initialState);
  });

  test("should scan sitemap manually", async ({ page }) => {
    await page.goto("/sitemaps");

    await page.click('button:has-text("Scan Now")').first();

    await expect(page.locator('[role="alert"]')).toContainText(
      "Sitemap queued for scanning"
    );
  });

  test("should delete sitemap", async ({ page }) => {
    await page.goto("/sitemaps");

    const rowCount = await page.locator("table tbody tr").count();

    // Click delete on first sitemap
    await page.click('button[aria-label="Delete"]').first();

    // Confirm deletion
    await page.click('button:has-text("Confirm")');

    await expect(page.locator('[role="alert"]')).toContainText(
      "Sitemap deleted"
    );

    // Row count should decrease
    const newRowCount = await page.locator("table tbody tr").count();
    expect(newRowCount).toBe(rowCount - 1);
  });
});
```

**File**: `tests/e2e/admin.spec.ts`

```typescript
import { test, expect } from "@playwright/test";

test.describe("Admin Panel", () => {
  test.beforeEach(async ({ page }) => {
    // Login as admin
    await page.goto("/login");
    await page.fill('input[name="email"]', "admin@example.com");
    await page.fill('input[name="password"]', "AdminPassword123");
    await page.click('button[type="submit"]');
  });

  test("should access admin panel", async ({ page }) => {
    await page.goto("/admin");
    await expect(page.locator("h1")).toContainText("Admin Dashboard");
  });

  test("should view users list", async ({ page }) => {
    await page.goto("/admin/users");
    await expect(page.locator("table")).toBeVisible();
    await expect(page.locator("table tbody tr").count()).toBeGreaterThan(0);
  });

  test("should search users", async ({ page }) => {
    await page.goto("/admin/users");

    await page.fill('input[placeholder*="Search"]', "test@example.com");
    await page.waitForTimeout(500);

    const rows = page.locator("table tbody tr");
    await expect(rows).toContainText("test@example.com");
  });

  test("should suspend user", async ({ page }) => {
    await page.goto("/admin/users");

    // Click suspend on first non-admin user
    await page.click('button:has-text("Suspend")').first();
    await page.click('button:has-text("Confirm")');

    await expect(page.locator('[role="alert"]')).toContainText(
      "User suspended"
    );
  });

  test("should view service workers", async ({ page }) => {
    await page.goto("/admin/workers");
    await expect(page.locator("h1")).toContainText("Service Workers");
  });

  test("should add service worker", async ({ page }) => {
    await page.goto("/admin/workers");

    await page.click('button:has-text("Add Worker")');
    await page.fill('input[name="name"]', "Test Worker");
    await page.fill('input[name="email"]', "worker@example.com");
    await page.fill('textarea[name="credentials"]', '{"type":"service_account"}');
    await page.click('button[type="submit"]');

    await expect(page.locator('[role="alert"]')).toContainText(
      "Worker added"
    );
  });

  test("should run manual jobs", async ({ page }) => {
    await page.goto("/admin/server");

    await page.click('button:has-text("Scan Sitemaps")');

    await expect(page.locator('[role="alert"]')).toContainText(
      "Job started"
    );
  });
});
```

---

## Component Tests

### React Component Tests

**File**: `tests/components/sitemap-list.test.tsx`

```typescript
import { describe, it, expect, vi } from "vitest";
import { render, screen, fireEvent, waitFor } from "@testing-library/react";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { SitemapList } from "@/components/dashboard/sitemap-list";

const queryClient = new QueryClient({
  defaultOptions: {
    queries: { retry: false },
  },
});

const wrapper = ({ children }: { children: React.ReactNode }) => (
  <QueryClientProvider client={queryClient}>{children}</QueryClientProvider>
);

describe("SitemapList", () => {
  it("should render loading state", () => {
    render(<SitemapList />, { wrapper });
    expect(screen.getByText("Loading...")).toBeInTheDocument();
  });

  it("should render sitemaps", async () => {
    const mockSitemaps = [
      {
        id: "1",
        url: "https://example.com/sitemap.xml",
        autoScan: true,
        _count: { urls: 100 },
      },
    ];

    (global.fetch as any) = vi.fn(() =>
      Promise.resolve({
        json: () => Promise.resolve({ sitemaps: mockSitemaps }),
      })
    );

    render(<SitemapList />, { wrapper });

    await waitFor(() => {
      expect(screen.getByText("example.com/sitemap.xml")).toBeInTheDocument();
    });
  });

  it("should toggle auto-scan", async () => {
    const mockSitemaps = [
      {
        id: "1",
        url: "https://example.com/sitemap.xml",
        autoScan: false,
        _count: { urls: 100 },
      },
    ];

    (global.fetch as any) = vi.fn()
      .mockResolvedValueOnce({
        json: () => Promise.resolve({ sitemaps: mockSitemaps }),
      })
      .mockResolvedValueOnce({
        json: () => Promise.resolve({ autoScan: true }),
      });

    render(<SitemapList />, { wrapper });

    await waitFor(() => {
      const toggle = screen.getByRole("switch");
      fireEvent.click(toggle);
    });

    await waitFor(() => {
      expect(global.fetch).toHaveBeenCalledWith(
        expect.stringContaining("/auto-scan"),
        expect.objectContaining({ method: "PUT" })
      );
    });
  });
});
```

---

## Coverage Requirements

### Coverage Thresholds

**File**: `vitest.config.ts` (coverage section)

```typescript
coverage: {
  provider: "v8",
  reporter: ["text", "json", "html", "lcov"],
  thresholds: {
    lines: 80,
    functions: 80,
    branches: 75,
    statements: 80,
  },
  exclude: [
    "node_modules/",
    "tests/",
    "**/*.d.ts",
    "**/*.config.*",
    "**/mockData.ts",
    ".next/",
  ],
}
```

### Running Tests

```bash
# Run all tests
pnpm test

# Run with coverage
pnpm test:coverage

# Run in watch mode
pnpm test:watch

# Run E2E tests
pnpm test:e2e

# Run specific test file
pnpm test sitemap.service.test.ts

# Run with UI
pnpm test:ui
```

---

This comprehensive testing guide ensures high code quality and catches bugs early in development.
