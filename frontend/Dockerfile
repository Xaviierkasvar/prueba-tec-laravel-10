FROM node:18-alpine

WORKDIR /app

# Copiar archivos de configuración
COPY package*.json ./

# Instalar dependencias
RUN npm install

# Copiar código fuente
COPY . .

# Exponer puerto
EXPOSE 5173

# Comando para iniciar la aplicación
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]