# Complete Configuration Files

This document contains all configuration files needed for the Next.js rewrite.

---

## next.config.js

```javascript
/** @type {import('next').NextConfig} */
const nextConfig = {
  reactStrictMode: true,

  // Enable SWC minification
  swcMinify: true,

  // Standalone output for Docker
  output: 'standalone',

  // Image optimization
  images: {
    domains: [
      'lh3.googleusercontent.com', // Google profile images
      'avatars.githubusercontent.com',
    ],
    formats: ['image/avif', 'image/webp'],
  },

  // Environment variables validation
  experimental: {
    typedRoutes: true,
  },

  // Headers for security
  async headers() {
    return [
      {
        source: '/:path*',
        headers: [
          {
            key: 'X-DNS-Prefetch-Control',
            value: 'on',
          },
          {
            key: 'Strict-Transport-Security',
            value: 'max-age=63072000; includeSubDomains; preload',
          },
          {
            key: 'X-Frame-Options',
            value: 'SAMEORIGIN',
          },
          {
            key: 'X-Content-Type-Options',
            value: 'nosniff',
          },
          {
            key: 'X-XSS-Protection',
            value: '1; mode=block',
          },
          {
            key: 'Referrer-Policy',
            value: 'strict-origin-when-cross-origin',
          },
          {
            key: 'Permissions-Policy',
            value: 'camera=(), microphone=(), geolocation=()',
          },
        ],
      },
    ];
  },

  // Redirects
  async redirects() {
    return [
      {
        source: '/home',
        destination: '/dashboard',
        permanent: true,
      },
    ];
  },

  // Webpack configuration
  webpack: (config, { isServer }) => {
    if (!isServer) {
      // Don't resolve 'fs' module on the client
      config.resolve.fallback = {
        ...config.resolve.fallback,
        fs: false,
        net: false,
        tls: false,
      };
    }
    return config;
  },
};

module.exports = nextConfig;
```

---

## tsconfig.json

```json
{
  "compilerOptions": {
    "target": "ES2022",
    "lib": ["dom", "dom.iterable", "esnext"],
    "allowJs": true,
    "skipLibCheck": true,
    "strict": true,
    "noEmit": true,
    "esModuleInterop": true,
    "module": "esnext",
    "moduleResolution": "bundler",
    "resolveJsonModule": true,
    "isolatedModules": true,
    "jsx": "preserve",
    "incremental": true,
    "plugins": [
      {
        "name": "next"
      }
    ],
    "paths": {
      "@/*": ["./src/*"],
      "@/components/*": ["./src/components/*"],
      "@/lib/*": ["./src/lib/*"],
      "@/services/*": ["./src/services/*"],
      "@/types/*": ["./src/types/*"],
      "@/hooks/*": ["./src/hooks/*"],
      "@/app/*": ["./src/app/*"]
    },
    "forceConsistentCasingInFileNames": true,
    "noUnusedLocals": true,
    "noUnusedParameters": true,
    "noFallthroughCasesInSwitch": true
  },
  "include": [
    "next-env.d.ts",
    "**/*.ts",
    "**/*.tsx",
    ".next/types/**/*.ts",
    "workers/**/*.ts"
  ],
  "exclude": ["node_modules"]
}
```

---

## tailwind.config.ts

```typescript
import type { Config } from "tailwindcss";

const config: Config = {
  darkMode: ["class"],
  content: [
    "./src/pages/**/*.{js,ts,jsx,tsx,mdx}",
    "./src/components/**/*.{js,ts,jsx,tsx,mdx}",
    "./src/app/**/*.{js,ts,jsx,tsx,mdx}",
  ],
  theme: {
    container: {
      center: true,
      padding: "2rem",
      screens: {
        "2xl": "1400px",
      },
    },
    extend: {
      colors: {
        border: "hsl(var(--border))",
        input: "hsl(var(--input))",
        ring: "hsl(var(--ring))",
        background: "hsl(var(--background))",
        foreground: "hsl(var(--foreground))",
        primary: {
          DEFAULT: "hsl(var(--primary))",
          foreground: "hsl(var(--primary-foreground))",
        },
        secondary: {
          DEFAULT: "hsl(var(--secondary))",
          foreground: "hsl(var(--secondary-foreground))",
        },
        destructive: {
          DEFAULT: "hsl(var(--destructive))",
          foreground: "hsl(var(--destructive-foreground))",
        },
        success: {
          DEFAULT: "hsl(var(--success))",
          foreground: "hsl(var(--success-foreground))",
        },
        warning: {
          DEFAULT: "hsl(var(--warning))",
          foreground: "hsl(var(--warning-foreground))",
        },
        muted: {
          DEFAULT: "hsl(var(--muted))",
          foreground: "hsl(var(--muted-foreground))",
        },
        accent: {
          DEFAULT: "hsl(var(--accent))",
          foreground: "hsl(var(--accent-foreground))",
        },
        popover: {
          DEFAULT: "hsl(var(--popover))",
          foreground: "hsl(var(--popover-foreground))",
        },
        card: {
          DEFAULT: "hsl(var(--card))",
          foreground: "hsl(var(--card-foreground))",
        },
      },
      borderRadius: {
        lg: "var(--radius)",
        md: "calc(var(--radius) - 2px)",
        sm: "calc(var(--radius) - 4px)",
      },
      keyframes: {
        "accordion-down": {
          from: { height: "0" },
          to: { height: "var(--radix-accordion-content-height)" },
        },
        "accordion-up": {
          from: { height: "var(--radix-accordion-content-height)" },
          to: { height: "0" },
        },
        "fade-in": {
          from: { opacity: "0" },
          to: { opacity: "1" },
        },
        "fade-out": {
          from: { opacity: "1" },
          to: { opacity: "0" },
        },
        "slide-in-from-top": {
          from: { transform: "translateY(-100%)" },
          to: { transform: "translateY(0)" },
        },
        "slide-in-from-bottom": {
          from: { transform: "translateY(100%)" },
          to: { transform: "translateY(0)" },
        },
        "slide-in-from-left": {
          from: { transform: "translateX(-100%)" },
          to: { transform: "translateX(0)" },
        },
        "slide-in-from-right": {
          from: { transform: "translateX(100%)" },
          to: { transform: "translateX(0)" },
        },
      },
      animation: {
        "accordion-down": "accordion-down 0.2s ease-out",
        "accordion-up": "accordion-up 0.2s ease-out",
        "fade-in": "fade-in 0.3s ease-out",
        "fade-out": "fade-out 0.3s ease-out",
        "slide-in-from-top": "slide-in-from-top 0.3s ease-out",
        "slide-in-from-bottom": "slide-in-from-bottom 0.3s ease-out",
        "slide-in-from-left": "slide-in-from-left 0.3s ease-out",
        "slide-in-from-right": "slide-in-from-right 0.3s ease-out",
      },
    },
  },
  plugins: [require("tailwindcss-animate")],
};

export default config;
```

---

## postcss.config.js

```javascript
module.exports = {
  plugins: {
    tailwindcss: {},
    autoprefixer: {},
  },
};
```

---

## .eslintrc.json

```json
{
  "extends": [
    "next/core-web-vitals",
    "plugin:@typescript-eslint/recommended",
    "prettier"
  ],
  "parser": "@typescript-eslint/parser",
  "parserOptions": {
    "ecmaVersion": 2022,
    "sourceType": "module",
    "project": "./tsconfig.json"
  },
  "plugins": ["@typescript-eslint", "tailwindcss"],
  "rules": {
    "@typescript-eslint/no-unused-vars": [
      "error",
      {
        "argsIgnorePattern": "^_",
        "varsIgnorePattern": "^_"
      }
    ],
    "@typescript-eslint/no-explicit-any": "warn",
    "@typescript-eslint/explicit-module-boundary-types": "off",
    "@typescript-eslint/no-non-null-assertion": "warn",
    "tailwindcss/classnames-order": "warn",
    "tailwindcss/no-custom-classname": "off",
    "react/display-name": "off",
    "no-console": ["warn", { "allow": ["warn", "error"] }]
  }
}
```

---

## .prettierrc

```json
{
  "semi": true,
  "trailingComma": "es5",
  "singleQuote": false,
  "printWidth": 80,
  "tabWidth": 2,
  "useTabs": false,
  "arrowParens": "always",
  "endOfLine": "lf",
  "plugins": ["prettier-plugin-tailwindcss"]
}
```

---

## .prettierignore

```
.next
node_modules
dist
build
coverage
.env*
pnpm-lock.yaml
package-lock.json
yarn.lock
*.min.js
*.min.css
public
.turbo
```

---

## .gitignore

```
# Dependencies
/node_modules
/.pnp
.pnp.js

# Testing
/coverage
*.lcov

# Next.js
/.next/
/out/

# Production
/build
/dist

# Misc
.DS_Store
*.pem

# Debug
npm-debug.log*
yarn-debug.log*
yarn-error.log*
.pnpm-debug.log*

# Local env files
.env
.env*.local
.env.development.local
.env.test.local
.env.production.local

# Vercel
.vercel

# TypeScript
*.tsbuildinfo
next-env.d.ts

# Prisma
prisma/migrations/**/migration.sql

# IDE
.vscode/*
!.vscode/settings.json
!.vscode/tasks.json
!.vscode/launch.json
!.vscode/extensions.json
.idea
*.swp
*.swo
*~

# OS
Thumbs.db

# Workers
workers/**/*.js
workers/**/*.js.map

# Test
test-results/
playwright-report/
playwright/.cache/
```

---

## .env.example

```bash
# ============================================================================
# APPLICATION
# ============================================================================
NODE_ENV=development
NEXT_PUBLIC_APP_URL=http://localhost:3000
NEXT_PUBLIC_APP_NAME="SERP Surfer"

# ============================================================================
# DATABASE
# ============================================================================
# PostgreSQL (recommended for production)
DATABASE_URL="postgresql://postgres:password@localhost:5432/serpsurfer?schema=public"

# MySQL (alternative)
# DATABASE_URL="mysql://root:password@localhost:3306/serpsurfer"

# SQLite (development only)
# DATABASE_URL="file:./dev.db"

# ============================================================================
# REDIS
# ============================================================================
# Local Redis
REDIS_URL="redis://localhost:6379"

# Upstash Redis (serverless alternative)
# UPSTASH_REDIS_REST_URL="https://..."
# UPSTASH_REDIS_REST_TOKEN="..."

# ============================================================================
# AUTHENTICATION (NextAuth.js)
# ============================================================================
# Generate with: openssl rand -base64 32
NEXTAUTH_SECRET="your-super-secret-key-here-at-least-32-characters-long"
NEXTAUTH_URL="http://localhost:3000"

# ============================================================================
# GOOGLE OAUTH & APIs
# ============================================================================
# OAuth credentials from Google Cloud Console
GOOGLE_CLIENT_ID="your-client-id.apps.googleusercontent.com"
GOOGLE_CLIENT_SECRET="your-client-secret"
GOOGLE_REDIRECT_URI="http://localhost:3000/api/auth/callback/google"

# Service account credentials path (optional)
GOOGLE_SERVICE_ACCOUNT_PATH="/path/to/service-account.json"

# ============================================================================
# EMAIL
# ============================================================================
# Resend (recommended)
RESEND_API_KEY="re_..."
EMAIL_FROM="noreply@yourdomain.com"

# SendGrid (alternative)
# SENDGRID_API_KEY="SG..."
# SENDGRID_FROM_EMAIL="noreply@yourdomain.com"

# SMTP (alternative)
# SMTP_HOST="smtp.gmail.com"
# SMTP_PORT="587"
# SMTP_USER="your-email@gmail.com"
# SMTP_PASSWORD="your-app-password"

# ============================================================================
# MONITORING & LOGGING
# ============================================================================
# Sentry (error tracking)
SENTRY_DSN="https://...@sentry.io/..."
SENTRY_AUTH_TOKEN="..."
SENTRY_PROJECT="serp-surfer"
SENTRY_ORG="your-org"

# Log level (debug, info, warn, error)
LOG_LEVEL="info"

# ============================================================================
# RATE LIMITING
# ============================================================================
# Upstash Rate Limiting
UPSTASH_REDIS_REST_URL="https://..."
UPSTASH_REDIS_REST_TOKEN="..."

# Rate limit configuration
RATE_LIMIT_MAX_REQUESTS="100"
RATE_LIMIT_WINDOW_MS="900000" # 15 minutes

# ============================================================================
# QUEUE CONFIGURATION
# ============================================================================
# BullMQ/Redis Queue settings
QUEUE_CONCURRENCY_URL_PROCESSING="5"
QUEUE_CONCURRENCY_SITEMAP_SCAN="3"
QUEUE_CONCURRENCY_INDEXING="10"
QUEUE_CONCURRENCY_STATUS_CHECK="2"

# Job retention
QUEUE_REMOVE_ON_COMPLETE_COUNT="100"
QUEUE_REMOVE_ON_COMPLETE_AGE="86400" # 24 hours in seconds
QUEUE_REMOVE_ON_FAIL_COUNT="1000"
QUEUE_REMOVE_ON_FAIL_AGE="604800" # 7 days in seconds

# ============================================================================
# FEATURE FLAGS
# ============================================================================
FEATURE_AUTO_SCAN_ENABLED="true"
FEATURE_EMAIL_NOTIFICATIONS_ENABLED="true"
FEATURE_INDEXING_API_ENABLED="true"

# ============================================================================
# SCRAPING CONFIGURATION
# ============================================================================
# User agent for Google scraping
SCRAPER_USER_AGENT="Mozilla/5.0 (compatible; SERPSurfer/1.0)"

# Delay between requests (milliseconds)
SCRAPER_DELAY="2000"

# Max retries for failed requests
SCRAPER_MAX_RETRIES="3"

# ============================================================================
# CLEANUP SETTINGS
# ============================================================================
# Hours before marking URL as stale
URL_STALE_THRESHOLD_HOURS="72"

# Days to keep completed queue items
QUEUE_RETENTION_DAYS="7"

# Days to keep job history
JOB_HISTORY_RETENTION_DAYS="30"

# ============================================================================
# ADMIN SETTINGS
# ============================================================================
# Admin email (gets admin role on first signup)
ADMIN_EMAIL="admin@yourdomain.com"

# ============================================================================
# DEVELOPMENT
# ============================================================================
# Skip SSL verification in development (not recommended for production)
NODE_TLS_REJECT_UNAUTHORIZED="1"

# Enable debug logging
DEBUG="prisma:*"

# ============================================================================
# PRODUCTION SETTINGS
# ============================================================================
# Enable telemetry
NEXT_TELEMETRY_DISABLED="0"

# Image optimization
NEXT_SHARP_PATH="/app/node_modules/sharp"

# ============================================================================
# DOCKER SETTINGS (when using Docker)
# ============================================================================
# Database (Docker Compose service name)
# DATABASE_URL="postgresql://postgres:password@db:5432/serpsurfer"

# Redis (Docker Compose service name)
# REDIS_URL="redis://redis:6379"
```

---

## .env.local (example for local development)

```bash
NODE_ENV=development
NEXT_PUBLIC_APP_URL=http://localhost:3000

DATABASE_URL="postgresql://postgres:password@localhost:5432/serpsurfer_dev"
REDIS_URL="redis://localhost:6379"

NEXTAUTH_SECRET="development-secret-change-in-production"
NEXTAUTH_URL="http://localhost:3000"

GOOGLE_CLIENT_ID="your-dev-client-id.apps.googleusercontent.com"
GOOGLE_CLIENT_SECRET="your-dev-client-secret"

RESEND_API_KEY="re_dev_..."
EMAIL_FROM="dev@localhost"

LOG_LEVEL="debug"
```

---

## .dockerignore

```
node_modules
npm-debug.log
.next
.env*.local
.git
.gitignore
README.md
.vscode
.idea
.DS_Store
coverage
.turbo
dist
build
```

---

## vitest.config.ts

```typescript
import { defineConfig } from "vitest/config";
import react from "@vitejs/plugin-react";
import tsconfigPaths from "vite-tsconfig-paths";
import path from "path";

export default defineConfig({
  plugins: [react(), tsconfigPaths()],
  test: {
    environment: "jsdom",
    globals: true,
    setupFiles: ["./tests/setup.ts"],
    coverage: {
      provider: "v8",
      reporter: ["text", "json", "html", "lcov"],
      exclude: [
        "node_modules/",
        "tests/",
        "**/*.d.ts",
        "**/*.config.*",
        "**/mockData.ts",
        ".next/",
      ],
    },
    include: ["**/*.{test,spec}.{js,mjs,cjs,ts,mts,cts,jsx,tsx}"],
  },
  resolve: {
    alias: {
      "@": path.resolve(__dirname, "./src"),
    },
  },
});
```

---

## playwright.config.ts

```typescript
import { defineConfig, devices } from "@playwright/test";

export default defineConfig({
  testDir: "./tests/e2e",
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: process.env.CI ? 1 : undefined,
  reporter: "html",

  use: {
    baseURL: process.env.NEXT_PUBLIC_APP_URL || "http://localhost:3000",
    trace: "on-first-retry",
    screenshot: "only-on-failure",
  },

  projects: [
    {
      name: "chromium",
      use: { ...devices["Desktop Chrome"] },
    },
    {
      name: "firefox",
      use: { ...devices["Desktop Firefox"] },
    },
    {
      name: "webkit",
      use: { ...devices["Desktop Safari"] },
    },
    {
      name: "Mobile Chrome",
      use: { ...devices["Pixel 5"] },
    },
    {
      name: "Mobile Safari",
      use: { ...devices["iPhone 12"] },
    },
  ],

  webServer: {
    command: "pnpm dev",
    url: "http://localhost:3000",
    reuseExistingServer: !process.env.CI,
  },
});
```

---

## .husky/pre-commit

```bash
#!/usr/bin/env sh
. "$(dirname -- "$0")/_/husky.sh"

pnpm lint-staged
```

---

## .lintstagedrc.js

```javascript
module.exports = {
  "*.{ts,tsx}": [
    "eslint --fix",
    "prettier --write",
    () => "tsc --noEmit",
  ],
  "*.{json,md}": ["prettier --write"],
};
```

---

## .vscode/settings.json

```json
{
  "editor.defaultFormatter": "esbenp.prettier-vscode",
  "editor.formatOnSave": true,
  "editor.codeActionsOnSave": {
    "source.fixAll.eslint": true
  },
  "typescript.tsdk": "node_modules/typescript/lib",
  "typescript.enablePromptUseWorkspaceTsdk": true,
  "tailwindCSS.experimental.classRegex": [
    ["cva\\(([^)]*)\\)", "[\"'`]([^\"'`]*).*?[\"'`]"],
    ["cn\\(([^)]*)\\)", "(?:'|\"|`)([^']*)(?:'|\"|`)"]
  ],
  "files.associations": {
    "*.css": "tailwindcss"
  },
  "[prisma]": {
    "editor.defaultFormatter": "Prisma.prisma"
  }
}
```

---

## .vscode/extensions.json

```json
{
  "recommendations": [
    "dbaeumer.vscode-eslint",
    "esbenp.prettier-vscode",
    "bradlc.vscode-tailwindcss",
    "Prisma.prisma",
    "ms-playwright.playwright",
    "vitest.explorer"
  ]
}
```

---

## sentry.client.config.ts

```typescript
import * as Sentry from "@sentry/nextjs";

Sentry.init({
  dsn: process.env.SENTRY_DSN,
  tracesSampleRate: 1.0,
  debug: false,
  environment: process.env.NODE_ENV,
  enabled: process.env.NODE_ENV === "production",
});
```

---

## sentry.server.config.ts

```typescript
import * as Sentry from "@sentry/nextjs";

Sentry.init({
  dsn: process.env.SENTRY_DSN,
  tracesSampleRate: 1.0,
  debug: false,
  environment: process.env.NODE_ENV,
  enabled: process.env.NODE_ENV === "production",
});
```

---

## sentry.edge.config.ts

```typescript
import * as Sentry from "@sentry/nextjs";

Sentry.init({
  dsn: process.env.SENTRY_DSN,
  tracesSampleRate: 1.0,
  debug: false,
  environment: process.env.NODE_ENV,
  enabled: process.env.NODE_ENV === "production",
});
```
