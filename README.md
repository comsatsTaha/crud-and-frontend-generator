# 🛠️ CRUD & Frontend Generator

A Laravel + Vue 3 starter kit with a custom Artisan command that scaffolds full-stack CRUD operations — including backend (Model, Migration, Controller, Requests, Routes) and frontend (Vue/Inertia pages styled with Tailwind CSS + shadcn-vue) — from a single command.

---

## ✨ Features

- **One-command CRUD scaffolding** via a custom `php artisan` command
- **Backend generation**: Model, Migration, Form Requests, Resource Controller, and Routes
- **Frontend generation**: Vue 3 + Inertia.js pages (Index, Create, Edit, Show) with Tailwind CSS
- **shadcn-vue components** out of the box for polished, accessible UI
- **Laravel 12** with PHP 8.2+ support
- **Vite + TypeScript** for fast frontend development
- **Pest** for testing
- **ESLint + Prettier** for code quality and formatting

---

## 🧰 Tech Stack

| Layer      | Technology                               |
|------------|------------------------------------------|
| Backend    | Laravel 12, PHP 8.2+                    |
| Frontend   | Vue 3, Inertia.js, TypeScript           |
| Styling    | Tailwind CSS, shadcn-vue, Radix Vue     |
| Build Tool | Vite                                     |
| Testing    | Pest                                     |
| Linting    | ESLint, Prettier                         |

---

## 📋 Requirements

- PHP >= 8.2
- Composer
- Node.js >= 18
- npm or yarn
- A supported database (SQLite, MySQL, PostgreSQL)

---

## 🚀 Installation

**1. Clone the repository**

```bash
git clone https://github.com/comsatsTaha/crud-and-frontend-generator.git
cd crud-and-frontend-generator
```

**2. Install PHP dependencies**

```bash
composer install
```

**3. Install Node dependencies**

```bash
npm install
```

**4. Configure environment**

```bash
cp .env.example .env
php artisan key:generate
```

Update `.env` with your database credentials.

**5. Run migrations**

```bash
php artisan migrate
```

**6. Start the development server**

```bash
composer run dev
```

This runs the Laravel server, queue listener, and Vite dev server concurrently.

Or run them separately:

```bash
php artisan serve
npm run dev
```

---

## ⚡ Usage — CRUD Generator

Use the custom Artisan command to scaffold a complete CRUD module for any model:

```bash
php artisan make:crud {ModelName}
```

**Example:**

```bash
php artisan make:crud Product
```

This single command generates:

| File | Location |
|------|----------|
| Model | `app/Models/Product.php` |
| Migration | `database/migrations/..._create_products_table.php` |
| Controller | `app/Http/Controllers/ProductController.php` |
| Form Requests | `app/Http/Requests/StoreProductRequest.php`, `UpdateProductRequest.php` |
| Vue Pages | `resources/js/Pages/Products/Index.vue`, `Create.vue`, `Edit.vue`, `Show.vue` |
| Routes | Appended to `routes/web.php` |

After generation, run:

```bash
php artisan migrate
```

Then visit `/products` in your browser to see the fully working CRUD interface.

---

## 📁 Project Structure

```
├── app/
│   ├── Console/
│   │   └── Commands/          # Custom Artisan CRUD command
│   ├── Http/
│   │   ├── Controllers/       # Generated resource controllers
│   │   └── Requests/          # Generated form request classes
│   └── Models/                # Generated Eloquent models
├── database/
│   └── migrations/            # Generated migration files
├── resources/
│   └── js/
│       ├── Components/        # Reusable Vue components (shadcn-vue)
│       │   └── ui/            # UI primitives
│       └── Pages/             # Inertia.js page components
├── routes/
│   └── web.php                # Routes (generator appends here)
├── stubs/
│   └── crud/                  # Stub templates for code generation
├── components.json            # shadcn-vue configuration
├── tailwind.config.js         # Tailwind CSS configuration
└── vite.config.ts             # Vite build configuration
```

---

## 🎨 Frontend Stack Details

The frontend is built on the Laravel Vue starter kit with enhancements:

- **Vue 3** with Composition API and `<script setup>`
- **Inertia.js** for SPA-like navigation without a separate API
- **shadcn-vue** (Radix Vue) for accessible, unstyled component primitives
- **Tailwind CSS** for utility-first styling
- **TypeScript** with `vue-tsc` for type safety
- **VueUse** for composable utilities
- **Lucide Vue Next** for icons
- **Prettier** with Tailwind class sorting

---

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Or with Pest directly
./vendor/bin/pest
```

---

## 🔧 Code Quality

```bash
# Format Vue/JS/TS files
npm run format

# Check formatting without writing
npm run format:check

# Lint and auto-fix
npm run lint

# Format PHP
./vendor/bin/pint
```

---

## 🔄 Customizing Stubs

The generator uses stub templates located in `stubs/crud/`. You can modify these to match your project's conventions:

```
stubs/
└── crud/
    ├── controller.stub
    ├── model.stub
    ├── migration.stub
    ├── request.stub
    └── vue-page.stub   # (and other page stubs)
```

Edit any stub file to change how generated files look — your changes will apply to all future `make:crud` calls.

---

## 🤝 Contributing

Contributions are welcome! Please:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/my-feature`)
3. Commit your changes (`git commit -m 'Add my feature'`)
4. Push to the branch (`git push origin feature/my-feature`)
5. Open a Pull Request

---

## 📄 License

This project is open-sourced under the [MIT license](https://opensource.org/licenses/MIT).

---

## 👤 Author

**comsatsTaha** — [GitHub Profile](https://github.com/comsatsTaha)
