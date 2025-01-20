// src/App.jsx
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import Login from './components/auth/Login';
import TaskManager from './components/tasks/TaskManager';
import PrivateRoute from './components/auth/PrivateRoute';
import PublicRoute from './components/auth/PublicRoute';

const App = () => {
  return (
    <BrowserRouter>
      <Routes>
        {/* Rutas públicas */}
        <Route
          path="/login"
          element={
            <PublicRoute>
              <Login />
            </PublicRoute>
          }
        />

        {/* Rutas protegidas */}
        <Route
          path="/tasks"
          element={
            <PrivateRoute>
              <TaskManager />
            </PrivateRoute>
          }
        />

        {/* Ruta por defecto */}
        <Route
          path="/"
          element={
            <PrivateRoute>
              <Navigate to="/tasks" replace />
            </PrivateRoute>
          }
        />

        {/* Ruta 404 - No encontrado */}
        <Route
          path="*"
          element={
            <PrivateRoute>
              <Navigate to="/tasks" replace />
            </PrivateRoute>
          }
        />
      </Routes>
    </BrowserRouter>
  );
};

export default App;