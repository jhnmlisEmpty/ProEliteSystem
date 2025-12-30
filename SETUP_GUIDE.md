# ProEliteSystem - Laravel 11 Setup Guide

## ✅ Installation Complete!

Your Laravel project with Livewire 3, Tailwind CSS, MySQL, and Heroicons is ready!

## 🎯 What's Installed

- **Laravel**: v12.44.0 (Latest)
- **Livewire**: v3.7.3
- **Tailwind CSS**: v4.1.18 (Pre-configured with Vite)
- **Heroicons**: v2.6.0 (Blade components)
- **Database**: MySQL configured

---

## 🗄️ Database Setup

### Create MySQL Database

You need to create the database. Open MySQL command line or phpMyAdmin:

```sql
CREATE DATABASE proelitesystemdatabase;
```

Or if you want to use a different database name, update `.env`:

```env
DB_DATABASE=your_database_name
```

### Run Migrations

Once the database is created:

```bash
php artisan migrate
```

---

## 🚀 Running the Development Server

### Start Vite (Frontend)

In a terminal, run:

```bash
npm run dev
```

This will start the Vite development server for hot-reloading CSS/JS.

### Start Laravel (Backend)

In another terminal, run:

```bash
php artisan serve
```

Your app will be available at: **http://localhost:8000**

---

## 📦 Using Livewire 3

### Create a Livewire Component

```bash
php artisan make:livewire ComponentName
```

This creates:
- Component class: `app/Livewire/ComponentName.php`
- View file: `resources/views/livewire/component-name.blade.php`

### Use in Blade Templates

```blade
<livewire:component-name />
```

---

## 🎨 Using Tailwind CSS

Tailwind is already configured with the new v4 syntax. Just use utility classes:

```blade
<div class="bg-blue-500 text-white p-4 rounded-lg">
    Hello Tailwind!
</div>
```

---

## 🎭 Using Heroicons

Heroicons are available as Blade components:

### Solid Icons
```blade
<x-heroicon-s-user class="w-6 h-6" />
<x-heroicon-s-home class="w-6 h-6" />
```

### Outline Icons
```blade
<x-heroicon-o-user class="w-6 h-6" />
<x-heroicon-o-home class="w-6 h-6" />
```

### Mini Icons
```blade
<x-heroicon-m-user class="w-5 h-5" />
<x-heroicon-m-home class="w-5 h-5" />
```

---

## 🛠️ Common Commands

### Artisan Commands
```bash
php artisan list                    # List all commands
php artisan make:model ModelName -m # Create model with migration
php artisan make:controller Name    # Create controller
php artisan migrate                 # Run migrations
php artisan migrate:fresh           # Drop all tables and re-migrate
php artisan tinker                  # Laravel REPL
php artisan route:list              # List all routes
```

### Livewire Commands
```bash
php artisan livewire:make ComponentName
php artisan livewire:layout          # Publish Livewire layout
```

### NPM Commands
```bash
npm run dev                         # Start Vite dev server
npm run build                       # Build for production
```

---

## 📁 Project Structure

```
ProEliteSystem/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   └── Middleware/
│   ├── Livewire/              # Livewire components
│   └── Models/
├── config/
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── css/
│   │   └── app.css            # Tailwind CSS
│   ├── js/
│   │   └── app.js
│   └── views/
│       ├── livewire/          # Livewire views
│       └── welcome.blade.php
├── routes/
│   └── web.php
└── public/
```

---

## 📝 Example: Create Your First Livewire Component

1. **Create the component:**
   ```bash
   php artisan make:livewire Counter
   ```

2. **Edit `app/Livewire/Counter.php`:**
   ```php
   <?php
   
   namespace App\Livewire;
   
   use Livewire\Component;
   
   class Counter extends Component
   {
       public $count = 0;
   
       public function increment()
       {
           $this->count++;
       }
   
       public function render()
       {
           return view('livewire.counter');
       }
   }
   ```

3. **Edit `resources/views/livewire/counter.blade.php`:**
   ```blade
   <div class="p-6 bg-white rounded-lg shadow-lg">
       <div class="flex items-center space-x-4">
           <x-heroicon-o-calculator class="w-8 h-8 text-blue-500" />
           <h2 class="text-2xl font-bold">Counter: {{ $count }}</h2>
       </div>
       <button 
           wire:click="increment" 
           class="mt-4 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
       >
           Increment
       </button>
   </div>
   ```

4. **Use it in `resources/views/welcome.blade.php`:**
   ```blade
   <!DOCTYPE html>
   <html>
   <head>
       <meta charset="utf-8">
       <meta name="viewport" content="width=device-width, initial-scale=1">
       <title>ProEliteSystem</title>
       @vite(['resources/css/app.css', 'resources/js/app.js'])
       @livewireStyles
   </head>
   <body class="bg-gray-100">
       <div class="container mx-auto py-10">
           <livewire:counter />
       </div>
       @livewireScripts
   </body>
   </html>
   ```

---

## 🔧 Troubleshooting

### Clear Cache
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### Livewire Not Working?
Make sure to include `@livewireStyles` and `@livewireScripts` in your layout.

### Tailwind Styles Not Showing?
Make sure Vite is running with `npm run dev`.

---

## 📚 Documentation Links

- [Laravel 11](https://laravel.com/docs/11.x)
- [Livewire 3](https://livewire.laravel.com)
- [Tailwind CSS](https://tailwindcss.com)
- [Heroicons](https://heroicons.com)

---

## 🎉 Next Steps

1. **Create your database** in MySQL
2. **Run migrations**: `php artisan migrate`
3. **Start dev servers**: `npm run dev` and `php artisan serve`
4. **Build something amazing!**

Happy coding! 🚀
