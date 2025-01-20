import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { API_URL, ENDPOINTS, MESSAGES } from '../../constants/config';

const Login = () => {
  const [credentials, setCredentials] = useState({
    email: '',
    password: ''
  });
  const [errors, setErrors] = useState({});
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();

  const validateForm = () => {
    const newErrors = {};
    
    // Validación de email
    if (!credentials.email.trim()) {
      newErrors.email = 'El correo electrónico es obligatorio';
    } else if (!/\S+@\S+\.\S+/.test(credentials.email)) {
      newErrors.email = 'Por favor, ingresa un correo electrónico válido';
    }

    // Validación de contraseña
    if (!credentials.password) {
      newErrors.password = 'La contraseña es obligatoria';
    } else if (credentials.password.length < 6) {
      newErrors.password = 'La contraseña debe tener al menos 6 caracteres';
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    if (!validateForm()) return;

    setLoading(true);
    try {
      const response = await fetch(`${API_URL}${ENDPOINTS.LOGIN}`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          email: credentials.email,
          password: credentials.password
        }),
      });

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.message || 'Credenciales inválidas');
      }

      localStorage.setItem('token', data.access_token);
      localStorage.setItem('user', JSON.stringify(data.user));
      navigate('/tasks');
    } catch (err) {
      setErrors({
        submit: err.message || 'Error al conectar con el servidor'
      });
    } finally {
      setLoading(false);
    }
  };

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setCredentials(prev => ({
      ...prev,
      [name]: value
    }));
    // Limpiar error del campo cuando el usuario empieza a escribir
    if (errors[name]) {
      setErrors(prev => ({
        ...prev,
        [name]: ''
      }));
    }
  };

  return (
    <div className="container-fluid bg-primary min-vh-100 d-flex align-items-center justify-content-center">
      <div className="card shadow-lg" style={{ maxWidth: '400px', width: '90%' }}>
        <div className="card-body p-4">
          <div className="text-center mb-4">
            <h1 className="h3 mb-3 fw-bold text-primary">PRUEBA-TEC-LARAVEL-10</h1>
            <p className="text-muted">Ingresa tus credenciales para acceder al sistema</p>
          </div>

          {errors.submit && (
            <div className="alert alert-danger" role="alert">
              {errors.submit}
            </div>
          )}

          <form onSubmit={handleSubmit} noValidate>
            <div className="mb-3">
              <label htmlFor="email" className="form-label">Correo Electrónico</label>
              <input
                type="email"
                className={`form-control ${errors.email ? 'is-invalid' : ''}`}
                id="email"
                name="email"
                value={credentials.email}
                onChange={handleInputChange}
                disabled={loading}
                required
                autoComplete="email"
              />
              {errors.email && (
                <div className="invalid-feedback">
                  {errors.email}
                </div>
              )}
            </div>

            <div className="mb-4">
              <label htmlFor="password" className="form-label">Contraseña</label>
              <input
                type="password"
                className={`form-control ${errors.password ? 'is-invalid' : ''}`}
                id="password"
                name="password"
                value={credentials.password}
                onChange={handleInputChange}
                disabled={loading}
                required
                autoComplete="current-password"
              />
              {errors.password && (
                <div className="invalid-feedback">
                  {errors.password}
                </div>
              )}
            </div>

            <button 
              type="submit" 
              className="btn btn-primary w-100" 
              disabled={loading}
            >
              {loading ? (
                <>
                  <span className="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                  Iniciando sesión...
                </>
              ) : (
                'Iniciar Sesión'
              )}
            </button>
          </form>
        </div>
      </div>
    </div>
  );
};

export default Login;