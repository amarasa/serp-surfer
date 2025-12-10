# Complete API Route Implementations

This document contains full implementations of all API routes for the Next.js rewrite.

---

## Table of Contents
1. [Authentication Routes](#authentication-routes)
2. [User Routes](#user-routes)
3. [Sitemap Routes](#sitemap-routes)
4. [URL Routes](#url-routes)
5. [Queue Routes](#queue-routes)
6. [Admin Routes](#admin-routes)
7. [Webhook Routes](#webhook-routes)

---

## Authentication Routes

### POST /api/auth/signup

**File**: `src/app/api/auth/signup/route.ts`

```typescript
import { NextRequest, NextResponse } from "next/server";
import { prisma } from "@/lib/db";
import { hash } from "bcryptjs";
import { z } from "zod";

const signupSchema = z.object({
  name: z.string().min(2, "Name must be at least 2 characters"),
  email: z.string().email("Invalid email address"),
  password: z
    .string()
    .min(8, "Password must be at least 8 characters")
    .regex(
      /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/,
      "Password must contain uppercase, lowercase, and number"
    ),
});

export async function POST(request: NextRequest) {
  try {
    const body = await request.json();
    const data = signupSchema.parse(body);

    // Check if user exists
    const existingUser = await prisma.user.findUnique({
      where: { email: data.email },
    });

    if (existingUser) {
      return NextResponse.json(
        { error: "User already exists" },
        { status: 400 }
      );
    }

    // Hash password
    const hashedPassword = await hash(data.password, 12);

    // Get or create user role
    let userRole = await prisma.role.findUnique({
      where: { name: "user" },
    });

    if (!userRole) {
      userRole = await prisma.role.create({
        data: { name: "user", description: "Regular user" },
      });
    }

    // Check if this should be admin (first user or matches ADMIN_EMAIL)
    const userCount = await prisma.user.count();
    const isFirstUser = userCount === 0;
    const isAdminEmail = data.email === process.env.ADMIN_EMAIL;

    let roleId = userRole.id;

    if (isFirstUser || isAdminEmail) {
      let adminRole = await prisma.role.findUnique({
        where: { name: "admin" },
      });

      if (!adminRole) {
        adminRole = await prisma.role.create({
          data: { name: "admin", description: "Administrator" },
        });
      }

      roleId = adminRole.id;
    }

    // Create user
    const user = await prisma.user.create({
      data: {
        name: data.name,
        email: data.email,
        password: hashedPassword,
        roleId,
      },
      select: {
        id: true,
        name: true,
        email: true,
        role: {
          select: {
            name: true,
          },
        },
      },
    });

    return NextResponse.json(
      {
        message: "User created successfully",
        user: {
          id: user.id,
          name: user.name,
          email: user.email,
          role: user.role.name,
        },
      },
      { status: 201 }
    );
  } catch (error) {
    if (error instanceof z.ZodError) {
      return NextResponse.json(
        { error: "Validation error", details: error.errors },
        { status: 400 }
      );
    }

    console.error("Signup error:", error);
    return NextResponse.json(
      { error: "Internal server error" },
      { status: 500 }
    );
  }
}
```

---

## User Routes

### GET /api/user/profile

**File**: `src/app/api/user/profile/route.ts`

```typescript
import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "@/lib/auth";
import { prisma } from "@/lib/db";

export async function GET(request: NextRequest) {
  const session = await getServerSession(authOptions);

  if (!session?.user) {
    return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
  }

  try {
    const user = await prisma.user.findUnique({
      where: { id: session.user.id },
      select: {
        id: true,
        name: true,
        email: true,
        image: true,
        createdAt: true,
        role: {
          select: {
            name: true,
          },
        },
        _count: {
          select: {
            sitemaps: true,
            urlLists: true,
            indexQueues: true,
          },
        },
      },
    });

    if (!user) {
      return NextResponse.json({ error: "User not found" }, { status: 404 });
    }

    return NextResponse.json(user);
  } catch (error) {
    console.error("Profile fetch error:", error);
    return NextResponse.json(
      { error: "Internal server error" },
      { status: 500 }
    );
  }
}

export async function PUT(request: NextRequest) {
  const session = await getServerSession(authOptions);

  if (!session?.user) {
    return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
  }

  try {
    const body = await request.json();
    const { name, image } = body;

    const user = await prisma.user.update({
      where: { id: session.user.id },
      data: {
        ...(name && { name }),
        ...(image && { image }),
      },
      select: {
        id: true,
        name: true,
        email: true,
        image: true,
      },
    });

    return NextResponse.json(user);
  } catch (error) {
    console.error("Profile update error:", error);
    return NextResponse.json(
      { error: "Internal server error" },
      { status: 500 }
    );
  }
}
```

### PUT /api/user/password

**File**: `src/app/api/user/password/route.ts`

```typescript
import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "@/lib/auth";
import { prisma } from "@/lib/db";
import { hash, compare } from "bcryptjs";
import { z } from "zod";

const passwordSchema = z.object({
  currentPassword: z.string().min(1, "Current password is required"),
  newPassword: z
    .string()
    .min(8, "Password must be at least 8 characters")
    .regex(
      /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/,
      "Password must contain uppercase, lowercase, and number"
    ),
});

export async function PUT(request: NextRequest) {
  const session = await getServerSession(authOptions);

  if (!session?.user) {
    return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
  }

  try {
    const body = await request.json();
    const data = passwordSchema.parse(body);

    // Get user with password
    const user = await prisma.user.findUnique({
      where: { id: session.user.id },
    });

    if (!user || !user.password) {
      return NextResponse.json(
        { error: "Cannot change password for OAuth users" },
        { status: 400 }
      );
    }

    // Verify current password
    const isValid = await compare(data.currentPassword, user.password);

    if (!isValid) {
      return NextResponse.json(
        { error: "Current password is incorrect" },
        { status: 400 }
      );
    }

    // Hash new password
    const hashedPassword = await hash(data.newPassword, 12);

    // Update password
    await prisma.user.update({
      where: { id: session.user.id },
      data: { password: hashedPassword },
    });

    return NextResponse.json({ message: "Password updated successfully" });
  } catch (error) {
    if (error instanceof z.ZodError) {
      return NextResponse.json(
        { error: "Validation error", details: error.errors },
        { status: 400 }
      );
    }

    console.error("Password update error:", error);
    return NextResponse.json(
      { error: "Internal server error" },
      { status: 500 }
    );
  }
}
```

---

## Sitemap Routes

### GET /api/sitemaps

**File**: `src/app/api/sitemaps/route.ts`

```typescript
import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "@/lib/auth";
import { prisma } from "@/lib/db";
import { z } from "zod";

export async function GET(request: NextRequest) {
  const session = await getServerSession(authOptions);

  if (!session?.user) {
    return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
  }

  try {
    const { searchParams } = new URL(request.url);
    const page = parseInt(searchParams.get("page") || "1");
    const limit = parseInt(searchParams.get("limit") || "10");
    const skip = (page - 1) * limit;

    const [sitemaps, total] = await Promise.all([
      prisma.sitemap.findMany({
        where: { userId: session.user.id },
        include: {
          _count: {
            select: { urls: true },
          },
        },
        orderBy: { createdAt: "desc" },
        skip,
        take: limit,
      }),
      prisma.sitemap.count({
        where: { userId: session.user.id },
      }),
    ]);

    return NextResponse.json({
      sitemaps,
      pagination: {
        page,
        limit,
        total,
        totalPages: Math.ceil(total / limit),
      },
    });
  } catch (error) {
    console.error("Sitemaps fetch error:", error);
    return NextResponse.json(
      { error: "Internal server error" },
      { status: 500 }
    );
  }
}

const createSitemapSchema = z.object({
  url: z.string().url("Invalid URL"),
  gscSiteUrl: z.string().url("Invalid GSC site URL").optional(),
  autoScan: z.boolean().optional().default(false),
});

export async function POST(request: NextRequest) {
  const session = await getServerSession(authOptions);

  if (!session?.user) {
    return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
  }

  try {
    const body = await request.json();
    const data = createSitemapSchema.parse(body);

    // Check if sitemap already exists
    const existing = await prisma.sitemap.findFirst({
      where: {
        userId: session.user.id,
        url: data.url,
      },
    });

    if (existing) {
      return NextResponse.json(
        { error: "Sitemap already exists" },
        { status: 400 }
      );
    }

    const sitemap = await prisma.sitemap.create({
      data: {
        userId: session.user.id,
        url: data.url,
        gscSiteUrl: data.gscSiteUrl,
        autoScan: data.autoScan,
      },
      include: {
        _count: {
          select: { urls: true },
        },
      },
    });

    return NextResponse.json(sitemap, { status: 201 });
  } catch (error) {
    if (error instanceof z.ZodError) {
      return NextResponse.json(
        { error: "Validation error", details: error.errors },
        { status: 400 }
      );
    }

    console.error("Sitemap creation error:", error);
    return NextResponse.json(
      { error: "Internal server error" },
      { status: 500 }
    );
  }
}
```

### GET /api/sitemaps/[id]

**File**: `src/app/api/sitemaps/[id]/route.ts`

```typescript
import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "@/lib/auth";
import { prisma } from "@/lib/db";

export async function GET(
  request: NextRequest,
  { params }: { params: { id: string } }
) {
  const session = await getServerSession(authOptions);

  if (!session?.user) {
    return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
  }

  try {
    const sitemap = await prisma.sitemap.findFirst({
      where: {
        id: params.id,
        userId: session.user.id,
      },
      include: {
        _count: {
          select: { urls: true },
        },
      },
    });

    if (!sitemap) {
      return NextResponse.json({ error: "Sitemap not found" }, { status: 404 });
    }

    return NextResponse.json(sitemap);
  } catch (error) {
    console.error("Sitemap fetch error:", error);
    return NextResponse.json(
      { error: "Internal server error" },
      { status: 500 }
    );
  }
}

export async function PUT(
  request: NextRequest,
  { params }: { params: { id: string } }
) {
  const session = await getServerSession(authOptions);

  if (!session?.user) {
    return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
  }

  try {
    const body = await request.json();
    const { autoScan } = body;

    const sitemap = await prisma.sitemap.updateMany({
      where: {
        id: params.id,
        userId: session.user.id,
      },
      data: {
        ...(typeof autoScan === "boolean" && { autoScan }),
      },
    });

    if (sitemap.count === 0) {
      return NextResponse.json({ error: "Sitemap not found" }, { status: 404 });
    }

    const updated = await prisma.sitemap.findUnique({
      where: { id: params.id },
    });

    return NextResponse.json(updated);
  } catch (error) {
    console.error("Sitemap update error:", error);
    return NextResponse.json(
      { error: "Internal server error" },
      { status: 500 }
    );
  }
}

export async function DELETE(
  request: NextRequest,
  { params }: { params: { id: string } }
) {
  const session = await getServerSession(authOptions);

  if (!session?.user) {
    return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
  }

  try {
    const deleted = await prisma.sitemap.deleteMany({
      where: {
        id: params.id,
        userId: session.user.id,
      },
    });

    if (deleted.count === 0) {
      return NextResponse.json({ error: "Sitemap not found" }, { status: 404 });
    }

    return NextResponse.json({ message: "Sitemap deleted successfully" });
  } catch (error) {
    console.error("Sitemap deletion error:", error);
    return NextResponse.json(
      { error: "Internal server error" },
      { status: 500 }
    );
  }
}
```

### POST /api/sitemaps/[id]/scan

**File**: `src/app/api/sitemaps/[id]/scan/route.ts`

```typescript
import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "@/lib/auth";
import { prisma } from "@/lib/db";
import { sitemapScanQueue } from "@/lib/queue/client";

export async function POST(
  request: NextRequest,
  { params }: { params: { id: string } }
) {
  const session = await getServerSession(authOptions);

  if (!session?.user) {
    return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
  }

  try {
    const sitemap = await prisma.sitemap.findFirst({
      where: {
        id: params.id,
        userId: session.user.id,
      },
    });

    if (!sitemap) {
      return NextResponse.json({ error: "Sitemap not found" }, { status: 404 });
    }

    // Add to queue
    await sitemapScanQueue.add("scan-sitemap", {
      sitemapId: sitemap.id,
      userId: session.user.id,
      url: sitemap.url,
    });

    // Update sitemap status
    await prisma.sitemap.update({
      where: { id: params.id },
      data: { isPending: true },
    });

    return NextResponse.json({
      message: "Sitemap queued for scanning",
      sitemapId: sitemap.id,
    });
  } catch (error) {
    console.error("Sitemap scan queue error:", error);
    return NextResponse.json(
      { error: "Internal server error" },
      { status: 500 }
    );
  }
}
```

### GET /api/sitemaps/[id]/urls

**File**: `src/app/api/sitemaps/[id]/urls/route.ts`

```typescript
import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "@/lib/auth";
import { prisma } from "@/lib/db";

export async function GET(
  request: NextRequest,
  { params }: { params: { id: string } }
) {
  const session = await getServerSession(authOptions);

  if (!session?.user) {
    return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
  }

  try {
    // Verify ownership
    const sitemap = await prisma.sitemap.findFirst({
      where: {
        id: params.id,
        userId: session.user.id,
      },
    });

    if (!sitemap) {
      return NextResponse.json({ error: "Sitemap not found" }, { status: 404 });
    }

    const { searchParams } = new URL(request.url);
    const page = parseInt(searchParams.get("page") || "1");
    const limit = parseInt(searchParams.get("limit") || "50");
    const indexed = searchParams.get("indexed");
    const skip = (page - 1) * limit;

    const where: any = { sitemapId: params.id };
    if (indexed !== null && indexed !== undefined) {
      where.indexed = indexed === "true";
    }

    const [urls, total] = await Promise.all([
      prisma.sitemapUrl.findMany({
        where,
        orderBy: { createdAt: "desc" },
        skip,
        take: limit,
      }),
      prisma.sitemapUrl.count({ where }),
    ]);

    return NextResponse.json({
      urls,
      pagination: {
        page,
        limit,
        total,
        totalPages: Math.ceil(total / limit),
      },
    });
  } catch (error) {
    console.error("Sitemap URLs fetch error:", error);
    return NextResponse.json(
      { error: "Internal server error" },
      { status: 500 }
    );
  }
}
```

### GET /api/google/sitemaps

**File**: `src/app/api/google/sitemaps/route.ts`

```typescript
import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "@/lib/auth";
import { fetchSitemaps } from "@/lib/google/search-console";

export async function GET(request: NextRequest) {
  const session = await getServerSession(authOptions);

  if (!session?.user) {
    return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
  }

  try {
    const sitemaps = await fetchSitemaps(session.user.id);
    return NextResponse.json({ sitemaps });
  } catch (error) {
    console.error("Google sitemaps fetch error:", error);
    return NextResponse.json(
      { error: "Failed to fetch sitemaps from Google Search Console" },
      { status: 500 }
    );
  }
}
```

---

## URL Routes

### GET /api/urls

**File**: `src/app/api/urls/route.ts`

```typescript
import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "@/lib/auth";
import { prisma } from "@/lib/db";

export async function GET(request: NextRequest) {
  const session = await getServerSession(authOptions);

  if (!session?.user) {
    return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
  }

  try {
    const { searchParams } = new URL(request.url);
    const page = parseInt(searchParams.get("page") || "1");
    const limit = parseInt(searchParams.get("limit") || "50");
    const indexed = searchParams.get("indexed");
    const search = searchParams.get("search");
    const skip = (page - 1) * limit;

    const where: any = { userId: session.user.id };

    if (indexed !== null && indexed !== undefined) {
      where.indexed = indexed === "true";
    }

    if (search) {
      where.url = {
        contains: search,
      };
    }

    const [urls, total] = await Promise.all([
      prisma.urlList.findMany({
        where,
        orderBy: { lastSeen: "desc" },
        skip,
        take: limit,
      }),
      prisma.urlList.count({ where }),
    ]);

    return NextResponse.json({
      urls,
      pagination: {
        page,
        limit,
        total,
        totalPages: Math.ceil(total / limit),
      },
    });
  } catch (error) {
    console.error("URLs fetch error:", error);
    return NextResponse.json(
      { error: "Internal server error" },
      { status: 500 }
    );
  }
}

export async function POST(request: NextRequest) {
  const session = await getServerSession(authOptions);

  if (!session?.user) {
    return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
  }

  try {
    const body = await request.json();
    const { url } = body;

    if (!url) {
      return NextResponse.json({ error: "URL is required" }, { status: 400 });
    }

    // Check if URL already exists
    const existing = await prisma.urlList.findFirst({
      where: {
        userId: session.user.id,
        url,
      },
    });

    if (existing) {
      return NextResponse.json({ error: "URL already tracked" }, { status: 400 });
    }

    const urlRecord = await prisma.urlList.create({
      data: {
        userId: session.user.id,
        url,
      },
    });

    // Queue for status check
    await prisma.indexQueue.create({
      data: {
        userId: session.user.id,
        url,
        type: "URL",
      },
    });

    return NextResponse.json(urlRecord, { status: 201 });
  } catch (error) {
    console.error("URL creation error:", error);
    return NextResponse.json(
      { error: "Internal server error" },
      { status: 500 }
    );
  }
}
```

### GET /api/urls/stats

**File**: `src/app/api/urls/stats/route.ts`

```typescript
import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "@/lib/auth";
import { prisma } from "@/lib/db";

export async function GET(request: NextRequest) {
  const session = await getServerSession(authOptions);

  if (!session?.user) {
    return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
  }

  try {
    const [total, indexed, notIndexed, stale] = await Promise.all([
      prisma.urlList.count({
        where: { userId: session.user.id },
      }),
      prisma.urlList.count({
        where: {
          userId: session.user.id,
          indexed: true,
        },
      }),
      prisma.urlList.count({
        where: {
          userId: session.user.id,
          indexed: false,
        },
      }),
      prisma.urlList.count({
        where: {
          userId: session.user.id,
          lastSeen: {
            lt: new Date(Date.now() - 48 * 60 * 60 * 1000), // 48 hours ago
          },
        },
      }),
    ]);

    const indexingRate =
      total > 0 ? Math.round((indexed / total) * 100) : 0;

    return NextResponse.json({
      total,
      indexed,
      notIndexed,
      stale,
      indexingRate,
    });
  } catch (error) {
    console.error("URL stats error:", error);
    return NextResponse.json(
      { error: "Internal server error" },
      { status: 500 }
    );
  }
}
```

---

## Queue Routes

### GET /api/queue

**File**: `src/app/api/queue/route.ts`

```typescript
import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "@/lib/auth";
import { prisma } from "@/lib/db";

export async function GET(request: NextRequest) {
  const session = await getServerSession(authOptions);

  if (!session?.user) {
    return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
  }

  try {
    const { searchParams } = new URL(request.url);
    const page = parseInt(searchParams.get("page") || "1");
    const limit = parseInt(searchParams.get("limit") || "20");
    const status = searchParams.get("status");
    const type = searchParams.get("type");
    const skip = (page - 1) * limit;

    const where: any = { userId: session.user.id };

    if (status) {
      where.status = status;
    }

    if (type) {
      where.type = type;
    }

    const [queueItems, total] = await Promise.all([
      prisma.indexQueue.findMany({
        where,
        orderBy: { createdAt: "desc" },
        skip,
        take: limit,
      }),
      prisma.indexQueue.count({ where }),
    ]);

    return NextResponse.json({
      queueItems,
      pagination: {
        page,
        limit,
        total,
        totalPages: Math.ceil(total / limit),
      },
    });
  } catch (error) {
    console.error("Queue fetch error:", error);
    return NextResponse.json(
      { error: "Internal server error" },
      { status: 500 }
    );
  }
}

export async function POST(request: NextRequest) {
  const session = await getServerSession(authOptions);

  if (!session?.user) {
    return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
  }

  try {
    const body = await request.json();
    const { url, type, sitemapId } = body;

    if (!url || !type) {
      return NextResponse.json(
        { error: "URL and type are required" },
        { status: 400 }
      );
    }

    const queueItem = await prisma.indexQueue.create({
      data: {
        userId: session.user.id,
        url,
        type,
        sitemapId,
      },
    });

    return NextResponse.json(queueItem, { status: 201 });
  } catch (error) {
    console.error("Queue creation error:", error);
    return NextResponse.json(
      { error: "Internal server error" },
      { status: 500 }
    );
  }
}
```

### POST /api/queue/[id]/retry

**File**: `src/app/api/queue/[id]/retry/route.ts`

```typescript
import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "@/lib/auth";
import { retryQueueItem } from "@/services/queue.service";

export async function POST(
  request: NextRequest,
  { params }: { params: { id: string } }
) {
  const session = await getServerSession(authOptions);

  if (!session?.user) {
    return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
  }

  try {
    await retryQueueItem(params.id);
    return NextResponse.json({ message: "Queue item retry scheduled" });
  } catch (error) {
    console.error("Queue retry error:", error);
    return NextResponse.json(
      { error: error instanceof Error ? error.message : "Internal server error" },
      { status: 500 }
    );
  }
}
```

---

## Admin Routes

### GET /api/admin/users

**File**: `src/app/api/admin/users/route.ts`

```typescript
import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "@/lib/auth";
import { prisma } from "@/lib/db";

export async function GET(request: NextRequest) {
  const session = await getServerSession(authOptions);

  if (session?.user.role !== "admin") {
    return NextResponse.json({ error: "Forbidden" }, { status: 403 });
  }

  try {
    const { searchParams } = new URL(request.url);
    const page = parseInt(searchParams.get("page") || "1");
    const limit = parseInt(searchParams.get("limit") || "20");
    const search = searchParams.get("search");
    const skip = (page - 1) * limit;

    const where: any = {};

    if (search) {
      where.OR = [
        { email: { contains: search } },
        { name: { contains: search } },
      ];
    }

    const [users, total] = await Promise.all([
      prisma.user.findMany({
        where,
        include: {
          role: {
            select: {
              name: true,
            },
          },
          _count: {
            select: {
              sitemaps: true,
              urlLists: true,
            },
          },
        },
        orderBy: { createdAt: "desc" },
        skip,
        take: limit,
      }),
      prisma.user.count({ where }),
    ]);

    // Remove sensitive data
    const sanitizedUsers = users.map((user) => {
      const { password, googleAccessToken, googleRefreshToken, ...rest } = user;
      return rest;
    });

    return NextResponse.json({
      users: sanitizedUsers,
      pagination: {
        page,
        limit,
        total,
        totalPages: Math.ceil(total / limit),
      },
    });
  } catch (error) {
    console.error("Admin users fetch error:", error);
    return NextResponse.json(
      { error: "Internal server error" },
      { status: 500 }
    );
  }
}
```

### POST /api/admin/users/[id]/suspend

**File**: `src/app/api/admin/users/[id]/suspend/route.ts`

```typescript
import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "@/lib/auth";
import { prisma } from "@/lib/db";

export async function POST(
  request: NextRequest,
  { params }: { params: { id: string } }
) {
  const session = await getServerSession(authOptions);

  if (session?.user.role !== "admin") {
    return NextResponse.json({ error: "Forbidden" }, { status: 403 });
  }

  try {
    // Prevent self-suspension
    if (params.id === session.user.id) {
      return NextResponse.json(
        { error: "Cannot suspend yourself" },
        { status: 400 }
      );
    }

    const user = await prisma.user.update({
      where: { id: params.id },
      data: {
        isSuspended: true,
        status: "SUSPENDED",
      },
    });

    return NextResponse.json({ message: "User suspended successfully", user });
  } catch (error) {
    console.error("User suspension error:", error);
    return NextResponse.json(
      { error: "Internal server error" },
      { status: 500 }
    );
  }
}
```

### GET /api/admin/stats

**File**: `src/app/api/admin/stats/route.ts`

```typescript
import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "@/lib/auth";
import { prisma } from "@/lib/db";

export async function GET(request: NextRequest) {
  const session = await getServerSession(authOptions);

  if (session?.user.role !== "admin") {
    return NextResponse.json({ error: "Forbidden" }, { status: 403 });
  }

  try {
    const [
      totalUsers,
      totalSitemaps,
      totalUrls,
      indexedUrls,
      pendingQueue,
      failedQueue,
      activeWorkers,
    ] = await Promise.all([
      prisma.user.count(),
      prisma.sitemap.count(),
      prisma.urlList.count(),
      prisma.urlList.count({ where: { indexed: true } }),
      prisma.indexQueue.count({ where: { status: "PENDING" } }),
      prisma.indexQueue.count({ where: { status: "FAILED" } }),
      prisma.serviceWorker.count({ where: { isActive: true } }),
    ]);

    const indexingRate =
      totalUrls > 0 ? Math.round((indexedUrls / totalUrls) * 100) : 0;

    return NextResponse.json({
      users: {
        total: totalUsers,
      },
      sitemaps: {
        total: totalSitemaps,
      },
      urls: {
        total: totalUrls,
        indexed: indexedUrls,
        notIndexed: totalUrls - indexedUrls,
        indexingRate,
      },
      queue: {
        pending: pendingQueue,
        failed: failedQueue,
      },
      workers: {
        active: activeWorkers,
      },
    });
  } catch (error) {
    console.error("Admin stats error:", error);
    return NextResponse.json(
      { error: "Internal server error" },
      { status: 500 }
    );
  }
}
```

This is the first part of the complete API routes documentation. Would you like me to continue with more routes including service workers, email templates, and webhook implementations?
