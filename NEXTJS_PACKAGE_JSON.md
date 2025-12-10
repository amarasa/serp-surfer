# Complete package.json Configuration

This is the complete `package.json` file for the Next.js rewrite with all dependencies and scripts.

```json
{
  "name": "serp-surfer-nextjs",
  "version": "1.0.0",
  "description": "SEO tool for managing and monitoring Google Search indexing",
  "private": true,
  "scripts": {
    "dev": "next dev",
    "build": "next build",
    "start": "next start",
    "lint": "next lint",
    "type-check": "tsc --noEmit",
    "format": "prettier --write \"**/*.{ts,tsx,md,json}\"",
    "format:check": "prettier --check \"**/*.{ts,tsx,md,json}\"",
    "test": "vitest run",
    "test:watch": "vitest",
    "test:ui": "vitest --ui",
    "test:coverage": "vitest run --coverage",
    "test:e2e": "playwright test",
    "test:e2e:ui": "playwright test --ui",
    "db:generate": "prisma generate",
    "db:push": "prisma db push",
    "db:migrate": "prisma migrate dev",
    "db:migrate:deploy": "prisma migrate deploy",
    "db:seed": "tsx prisma/seed.ts",
    "db:studio": "prisma studio",
    "db:reset": "prisma migrate reset",
    "worker": "tsx workers/index.ts",
    "worker:dev": "tsx watch workers/index.ts",
    "scheduler": "tsx workers/scheduler.ts",
    "scheduler:dev": "tsx watch workers/scheduler.ts",
    "postinstall": "prisma generate",
    "prepare": "husky install",
    "docker:build": "docker build -t serp-surfer .",
    "docker:up": "docker-compose up -d",
    "docker:down": "docker-compose down",
    "docker:logs": "docker-compose logs -f",
    "migrate:from-laravel": "tsx scripts/migrate-from-laravel.ts"
  },
  "dependencies": {
    "@auth/prisma-adapter": "^2.0.0",
    "@bull-board/api": "^5.14.2",
    "@bull-board/express": "^5.14.2",
    "@bull-board/ui": "^5.14.2",
    "@hookform/resolvers": "^3.3.4",
    "@prisma/client": "^5.9.1",
    "@radix-ui/react-accordion": "^1.1.2",
    "@radix-ui/react-alert-dialog": "^1.0.5",
    "@radix-ui/react-avatar": "^1.0.4",
    "@radix-ui/react-checkbox": "^1.0.4",
    "@radix-ui/react-dialog": "^1.0.5",
    "@radix-ui/react-dropdown-menu": "^2.0.6",
    "@radix-ui/react-label": "^2.0.2",
    "@radix-ui/react-popover": "^1.0.7",
    "@radix-ui/react-progress": "^1.0.3",
    "@radix-ui/react-radio-group": "^1.1.3",
    "@radix-ui/react-select": "^2.0.0",
    "@radix-ui/react-separator": "^1.0.3",
    "@radix-ui/react-slider": "^1.1.2",
    "@radix-ui/react-slot": "^1.0.2",
    "@radix-ui/react-switch": "^1.0.3",
    "@radix-ui/react-tabs": "^1.0.4",
    "@radix-ui/react-toast": "^1.1.5",
    "@radix-ui/react-tooltip": "^1.0.7",
    "@sentry/nextjs": "^7.99.0",
    "@t3-oss/env-nextjs": "^0.9.2",
    "@tanstack/react-query": "^5.20.2",
    "@tanstack/react-query-devtools": "^5.20.2",
    "@tanstack/react-table": "^8.11.8",
    "@upstash/ratelimit": "^1.0.1",
    "@upstash/redis": "^1.28.4",
    "axios": "^1.6.7",
    "bcryptjs": "^2.4.3",
    "bullmq": "^5.2.0",
    "cheerio": "^1.0.0-rc.12",
    "class-variance-authority": "^0.7.0",
    "clsx": "^2.1.0",
    "cmdk": "^0.2.1",
    "date-fns": "^3.3.1",
    "fast-xml-parser": "^4.3.4",
    "googleapis": "^133.0.0",
    "grapesjs": "^0.21.12",
    "grapesjs-preset-newsletter": "^1.0.4",
    "ioredis": "^5.3.2",
    "jotai": "^2.6.4",
    "lucide-react": "^0.323.0",
    "next": "14.1.0",
    "next-auth": "^5.0.0-beta.4",
    "next-themes": "^0.2.1",
    "node-cron": "^3.0.3",
    "p-limit": "^5.0.0",
    "pino": "^8.18.0",
    "pino-pretty": "^10.3.1",
    "react": "^18.2.0",
    "react-dom": "^18.2.0",
    "react-email": "^2.1.0",
    "react-hook-form": "^7.50.1",
    "recharts": "^2.12.0",
    "resend": "^3.2.0",
    "sharp": "^0.33.2",
    "sonner": "^1.4.0",
    "superjson": "^2.2.1",
    "tailwind-merge": "^2.2.1",
    "tailwindcss-animate": "^1.0.7",
    "vaul": "^0.9.0",
    "zod": "^3.22.4",
    "zustand": "^4.5.0"
  },
  "devDependencies": {
    "@playwright/test": "^1.41.2",
    "@types/bcryptjs": "^2.4.6",
    "@types/node": "^20.11.16",
    "@types/node-cron": "^3.0.11",
    "@types/react": "^18.2.52",
    "@types/react-dom": "^18.2.18",
    "@typescript-eslint/eslint-plugin": "^6.20.0",
    "@typescript-eslint/parser": "^6.20.0",
    "@vitejs/plugin-react": "^4.2.1",
    "@vitest/ui": "^1.2.2",
    "autoprefixer": "^10.4.17",
    "dotenv-cli": "^7.3.0",
    "eslint": "^8.56.0",
    "eslint-config-next": "14.1.0",
    "eslint-config-prettier": "^9.1.0",
    "eslint-plugin-tailwindcss": "^3.14.2",
    "husky": "^9.0.10",
    "lint-staged": "^15.2.1",
    "postcss": "^8.4.33",
    "prettier": "^3.2.5",
    "prettier-plugin-tailwindcss": "^0.5.11",
    "prisma": "^5.9.1",
    "tailwindcss": "^3.4.1",
    "ts-node": "^10.9.2",
    "tsconfig-paths": "^4.2.0",
    "tsx": "^4.7.0",
    "typescript": "^5.3.3",
    "vite-tsconfig-paths": "^4.3.1",
    "vitest": "^1.2.2"
  },
  "engines": {
    "node": ">=20.0.0",
    "pnpm": ">=8.0.0"
  },
  "packageManager": "pnpm@8.15.1"
}
```

## Dependency Explanations

### Core Framework
- **next** (14.1.0) - Next.js framework with App Router
- **react** / **react-dom** - React 18 with concurrent features
- **typescript** - Type safety across the codebase

### Authentication & Authorization
- **next-auth** (v5 beta) - Authentication with Google OAuth
- **@auth/prisma-adapter** - NextAuth adapter for Prisma
- **bcryptjs** - Password hashing

### Database & ORM
- **@prisma/client** - Prisma client for database operations
- **prisma** (dev) - Prisma CLI for migrations and management

### Queue & Background Jobs
- **bullmq** - Redis-based queue system
- **ioredis** - Redis client for BullMQ
- **@bull-board/\*** - Queue monitoring UI (Horizon equivalent)
- **node-cron** - Cron job scheduler

### UI Components & Styling
- **@radix-ui/\*** - Unstyled, accessible UI primitives
- **tailwindcss** - Utility-first CSS framework
- **tailwindcss-animate** - Animation utilities
- **lucide-react** - Icon library
- **class-variance-authority** - Component variants
- **clsx** / **tailwind-merge** - Conditional classes

### State Management
- **@tanstack/react-query** - Server state management
- **zustand** or **jotai** - Client state management

### Forms & Validation
- **react-hook-form** - Form management
- **zod** - Schema validation
- **@hookform/resolvers** - RHF + Zod integration

### Data Tables
- **@tanstack/react-table** - Headless table library

### Google APIs
- **googleapis** - Official Google API client
- **axios** - HTTP client for web requests
- **cheerio** - HTML parsing for scraping

### Email
- **resend** - Email delivery service
- **react-email** - React-based email templates
- **grapesjs** - Visual email builder
- **grapesjs-preset-newsletter** - Newsletter templates

### XML/HTML Processing
- **fast-xml-parser** - Parse sitemap XML
- **cheerio** - DOM manipulation for scraping

### Utilities
- **date-fns** - Date manipulation
- **p-limit** - Concurrency control
- **superjson** - Serialize complex types

### Logging & Monitoring
- **pino** / **pino-pretty** - Fast logging
- **@sentry/nextjs** - Error tracking

### Rate Limiting
- **@upstash/ratelimit** - Redis-based rate limiting
- **@upstash/redis** - Serverless Redis client

### Testing
- **vitest** - Fast unit testing
- **@vitest/ui** - Vitest UI
- **@playwright/test** - E2E testing
- **@vitejs/plugin-react** - Vite React support

### Development Tools
- **tsx** - TypeScript execution
- **ts-node** - TypeScript Node.js
- **husky** - Git hooks
- **lint-staged** - Staged file linting
- **prettier** - Code formatting
- **eslint** - Linting

### Theming
- **next-themes** - Dark mode support

### Toast Notifications
- **sonner** - Toast notifications

### Additional UI
- **cmdk** - Command menu
- **recharts** - Charts library
- **vaul** - Drawer component

## NPM Scripts Explained

### Development
- `dev` - Start Next.js development server
- `build` - Build for production
- `start` - Start production server
- `lint` - Run ESLint
- `type-check` - TypeScript type checking
- `format` - Format code with Prettier
- `format:check` - Check code formatting

### Testing
- `test` - Run unit tests
- `test:watch` - Run tests in watch mode
- `test:ui` - Vitest UI
- `test:coverage` - Generate coverage report
- `test:e2e` - Run E2E tests
- `test:e2e:ui` - Playwright UI

### Database
- `db:generate` - Generate Prisma client
- `db:push` - Push schema to database
- `db:migrate` - Create migration
- `db:migrate:deploy` - Deploy migrations
- `db:seed` - Seed database
- `db:studio` - Open Prisma Studio
- `db:reset` - Reset database

### Workers
- `worker` - Start worker process
- `worker:dev` - Worker with auto-reload
- `scheduler` - Start scheduler
- `scheduler:dev` - Scheduler with auto-reload

### Docker
- `docker:build` - Build Docker image
- `docker:up` - Start containers
- `docker:down` - Stop containers
- `docker:logs` - View logs

### Migration
- `migrate:from-laravel` - Migrate data from Laravel

### Hooks
- `postinstall` - Run after npm install (generates Prisma client)
- `prepare` - Setup Husky

## Package Manager Configuration

Using **pnpm** for:
- Faster installations
- Disk space efficiency
- Strict dependency resolution
- Better monorepo support

Alternative: You can use **npm** or **yarn** by removing the `packageManager` field and adjusting scripts.
