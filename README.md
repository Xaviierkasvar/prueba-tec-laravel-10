# Prueba Técnica Laravel 10

Este proyecto es una aplicación full-stack que utiliza Laravel 10 para el backend y React para el frontend. La aplicación incluye autenticación JWT y gestión de tareas.

## Estructura del Proyecto

```
prueba-tec-laravel-10/
├── backend/                 # Aplicación Laravel
│   ├── app/
│   ├── database/
│   ├── tests/
│   ├── .env
│   ├── composer.json
│   └── Dockerfile
├── frontend/               # Aplicación React
│   ├── src/
│   ├── public/
│   ├── package.json
│   └── Dockerfile
├── docker-compose.yml
└── README.md
```

## Requisitos Previos

- Docker
- Docker Compose
- Git

## Instalación y Configuración

1. Clonar el repositorio:
```bash
git clone https://github.com/Xaviierkasvar/prueba-tec-laravel-10.git
cd prueba-tec-laravel-10
```

2. Configurar variables de entorno:
   - Copiar `.env.example` a `.env` en el backend:
```bash
cd backend
cp .env.example .env
```

3. Construir y levantar los contenedores:
```bash
docker-compose build
docker-compose up -d
```

4. Configurar el backend:
```bash
# Acceder al contenedor del backend
docker-compose exec backend bash

# Dentro del contenedor:
composer install
php artisan key:generate
php artisan jwt:secret
php artisan migrate:fresh --seed
```

## Ejecución de Pruebas

### Pruebas del Backend

```bash
# Acceder al contenedor del backend
docker-compose exec backend bash

# Ejecutar todas las pruebas
php artisan test

# Ejecutar pruebas específicas
php artisan test --filter AuthControllerTest
php artisan test --filter TaskControllerTest
```

## Acceso a la Aplicación

- Frontend: http://localhost:5173
- Backend API: http://localhost:8000/api
- Documentación Swagger/OpenAPI: http://localhost:8000/api/documentation/

### Credenciales de Prueba

```
Email: admin@example.com
Password: password123
```

## Comandos Útiles

### Docker

```bash
# Iniciar contenedores
docker-compose up -d

# Detener contenedores
docker-compose down

# Ver logs
docker-compose logs -f

# Reconstruir contenedores
docker-compose up -d --build
```

### Backend (Laravel)

```bash
# Acceder al contenedor
docker-compose exec backend bash

# Ejecutar migraciones
php artisan migrate

# Crear nuevos seeders
php artisan db:seed

# Limpiar cache
php artisan config:clear
php artisan cache:clear
```

### Frontend (React)

```bash
# Acceder al contenedor
docker-compose exec frontend sh

# Instalar nuevas dependencias
npm install <package-name>

# Construir para producción
npm run build
```

## Endpoints API

### Autenticación

```
POST /api/login
POST /api/logout
POST /api/refresh
GET /api/profile
```

### Tareas

```
GET /api/tasks
POST /api/tasks
GET /api/tasks/{id}
PUT /api/tasks/{id}
DELETE /api/tasks/{id}
PUT /api/tasks/{id}/toggle-status
```

## Solución de Problemas

1. Si la base de datos SQLite no se crea automáticamente:
```bash
docker-compose exec backend bash
touch database/database.sqlite
chmod 666 database/database.sqlite
php artisan migrate:fresh --seed
```

2. Si hay problemas con los permisos:
```bash
docker-compose exec backend bash
chmod -R 777 storage
chmod -R 777 bootstrap/cache
```

3. Si el frontend no puede conectar con el backend:
   - Verificar que las URLs en el frontend coincidan con la configuración del backend
   - Asegurarse de que los puertos estén correctamente mapeados en docker-compose.yml

## Contribución

1. Fork del repositorio
2. Crear una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit de tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abrir un Pull Request

## Licencia

Este proyecto está bajo la Licencia MIT - ver el archivo [LICENSE.md](LICENSE.md) para más detalles.