# 🛠️ CRUD & Frontend Generator

A Laravel + Vue 3 application that scaffolds full-stack CRUD operations through a **visual dashboard** — no terminal commands, no manual file creation. Fill in your model details, drag and drop your fields, and let the generator do the rest.

---

## ✨ Features

- **Visual dashboard** — generate CRUD entirely from the browser
- **Drag & drop field builder** — add, reorder, and configure fields visually
- **Field types & relationships** — define column types and model relationships through the UI
- **Backend generation** — Model, Migration, Form Requests, Resource Controller, and Routes
- **Frontend generation** — Vue 3 + Inertia.js pages (Index, Create, Edit, Show) with Tailwind CSS
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

## ⚡ Usage — Dashboard Generator

Once the app is running, open it in your browser and navigate to the generator dashboard.

### Step 1 — Enter Model Details

Type in your model name (e.g. `Product`) and any other top-level settings.

### Step 2 — Build Your Fields

Use the **drag & drop field builder** to:
- Add fields and set their **column types** (string, integer, boolean, text, etc.)
- Reorder fields by dragging them into place
- Define **relationships** (hasMany, belongsTo, etc.) between models

### Step 3 — Generate

Hit **Generate** and the dashboard produces:

| Generated File | Description |
|----------------|-------------|
| **Model** | Eloquent model with fillable fields and relationships |
| **Migration** | Database migration with all defined columns |
| **Controller** | Resource controller with full CRUD methods |
| **Form Requests** | Store and Update request classes with validation |
| **Routes** | Resource routes registered automatically |
| **Vue Pages** | Index, Create, Edit, and Show pages styled with Tailwind CSS + shadcn-vue |

No terminal. No manual editing. Open your new resource in the browser and start building on top of it.

---

## 📁 Project Structure

```
├── app/
│   ├── Http/
│   │   ├── Controllers/       # Generator controller + generated resource controllers
│   │   └── Requests/          # Generated form request classes
│   └── Models/                # Generated Eloquent models
├── database/
│   └── migrations/            # Generated migration files
├── resources/
│   └── js/
│       ├── Components/        # Reusable Vue components (shadcn-vue)
│       │   └── ui/            # UI primitives
│       └── Pages/             # Dashboard UI + generated Inertia.js pages
├── routes/
│   └── web.php                # Routes (generator registers here)
├── stubs/
│   └── crud/                  # Stub templates used by the generator
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

The generator uses stub templates located in `stubs/crud/`. You can modify these to match your project's conventions — your changes will apply to everything generated from the dashboard going forward.

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
