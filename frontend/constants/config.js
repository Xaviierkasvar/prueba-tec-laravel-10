export const API_URL = 'http://localhost:8000/api';

export const ENDPOINTS = {
    LOGIN: '/login',
    TASKS: '/tasks'
};

export const MESSAGES = {
    LOGIN: {
        REQUIRED_USER: 'El usuario es requerido',
        REQUIRED_PASSWORD: 'La contraseña es requerida',
        MIN_PASSWORD: 'La contraseña debe tener al menos 6 caracteres',
        INVALID_CREDENTIALS: 'Usuario o contraseña incorrectos',
        SERVER_ERROR: 'Error en el servidor, intente más tarde'
    }
};