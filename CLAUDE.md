# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Architecture

This is a **Laravel + Vue + Inertia.js** application with the following stack:
- **Backend**: Laravel 12 with Fortify for authentication
- **Frontend**: Vue 3 with TypeScript using Inertia.js for SPA-like experience
- **Styling**: Tailwind CSS 4 with shadcn/ui components via reka-ui
- **Build**: Vite with Laravel Vite plugin
- **Testing**: Pest PHP for backend, no frontend test framework configured

## Key Architecture Patterns

- **Inertia.js**: Server-side routing with client-side navigation. Pages are in `resources/js/pages/`
- **Component Structure**:
  - UI components in `resources/js/components/ui/` (shadcn/ui style)
  - App components in `resources/js/components/`
  - Layouts in `resources/js/layouts/`
- **Authentication**: Laravel Fortify handles auth with 2FA support
- **State Management**: Vue composables in `resources/js/composables/`

## Development Commands

### Frontend (npm)
- `npm run dev` - Start Vite dev server
- `npm run build` - Production build
- `npm run build:ssr` - SSR build
- `npm run lint` - ESLint with auto-fix
- `npm run format` - Prettier formatting
- `npm run format:check` - Check formatting

### Backend (Composer)
- `composer dev` - Full dev environment (Laravel server + queue + logs + Vite)
- `composer dev:ssr` - Dev environment with SSR
- `composer test` - Run Pest tests

### Laravel Artisan
- `php artisan serve` - Start Laravel server
- `php artisan test` - Run tests
- `php artisan pail` - Real-time log monitoring

## Code Conventions

- **Vue Components**: Single File Components with `<script setup lang="ts">`
- **Styling**: Tailwind utility classes, component variants with `class-variance-authority`
- **TypeScript**: Strict typing, types in `resources/js/types/`
- **Laravel**: Standard Laravel conventions, controllers organized by feature area
- **Formatting**: Prettier with organize-imports plugin, import organization enabled

## Project Structure

- `app/Http/Controllers/Auth/` - Authentication controllers
- `app/Http/Controllers/Settings/` - User settings controllers
- `resources/js/pages/auth/` - Authentication pages
- `resources/js/pages/settings/` - Settings pages
- `resources/js/layouts/` - Page layouts (app, auth, settings)
- `resources/js/components/ui/` - Reusable UI components
- `database/` - Migrations, seeders, factories