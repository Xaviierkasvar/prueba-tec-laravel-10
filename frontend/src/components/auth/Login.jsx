import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { API_URL, ENDPOINTS } from '../../constants/config';

const Login = () => {
  const [credentials, setCredentials] = useState({
    email: '',
    password: ''
  });
  const [errors, setErrors] = useState({});
  const [loading, setLoading] = useState(false);
  const [showRegisterModal, setShowRegisterModal] = useState(false);
  const navigate = useNavigate();

  const [registerData, setRegisterData] = useState({
    name: '',
    email: '',
    password: '',
    password_confirmation: ''
  });
  const [registerErrors, setRegisterErrors] = useState({});
  const [registerLoading, setRegisterLoading] = useState(false);

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

  const handleRegisterChange = (e) => {
    const { name, value } = e.target;
    setRegisterData(prev => ({
      ...prev,
      [name]: value
    }));
    
    // Limpiar errores cuando el usuario empieza a escribir
    if (registerErrors[name]) {
      setRegisterErrors(prev => ({
        ...prev,
        [name]: ''
      }));
    }
  };

  const handleRegisterSubmit = async (e) => {
    e.preventDefault();
    setRegisterLoading(true);
    setRegisterErrors({});

    try {
      const response = await fetch(`${API_URL}${ENDPOINTS.REGISTER}`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(registerData)
      });

      const data = await response.json();

      if (!response.ok) {
        // Manejar errores de validación
        if (data.errors) {
          setRegisterErrors(data.errors);
        }
        throw new Error(data.message || 'Error al registrar');
      }

      // Guardar token y usuario
      localStorage.setItem('token', data.access_token);
      localStorage.setItem('user', JSON.stringify(data.user));

      // Cerrar modal y navegar
      setShowRegisterModal(false);
      navigate('/tasks');
    } catch (err) {
      setRegisterErrors({
        submit: err.message
      });
    } finally {
      setRegisterLoading(false);
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

            <div className="d-grid gap-2">
              <button 
                type="submit" 
                className="btn btn-primary" 
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

              <div className="text-center mt-2">
                <span className="text-muted me-2">¿No tienes una cuenta?</span>
                <button 
                  type="button" 
                  className="btn btn-link p-0" 
                  onClick={() => setShowRegisterModal(true)}
                >
                  Registrarse
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>

      {/* Modal de Registro */}
      <div 
        className={`modal fade ${showRegisterModal ? 'show d-block' : ''}`} 
        tabIndex="-1" 
        style={{ backgroundColor: showRegisterModal ? 'rgba(0,0,0,0.5)' : 'transparent' }}
      >
        <div className="modal-dialog">
          <div className="modal-content">
            <div className="modal-header">
              <h5 className="modal-title">Registro de Usuario</h5>
              <button 
                type="button" 
                className="btn-close" 
                onClick={() => setShowRegisterModal(false)}
              ></button>
            </div>
            <form onSubmit={handleRegisterSubmit}>
              <div className="modal-body">
                <div className="mb-3">
                  <label htmlFor="register-name" className="form-label">Nombre</label>
                  <input 
                    type="text" 
                    className={`form-control ${registerErrors.name ? 'is-invalid' : ''}`}
                    id="register-name"
                    name="name"
                    value={registerData.name}
                    onChange={handleRegisterChange}
                    required
                  />
                  {registerErrors.name && (
                    <div className="invalid-feedback">
                      {registerErrors.name[0]}
                    </div>
                  )}
                </div>
                <div className="mb-3">
                  <label htmlFor="register-email" className="form-label">Correo Electrónico</label>
                  <input 
                    type="email" 
                    className={`form-control ${registerErrors.email ? 'is-invalid' : ''}`}
                    id="register-email"
                    name="email"
                    value={registerData.email}
                    onChange={handleRegisterChange}
                    required
                  />
                  {registerErrors.email && (
                    <div className="invalid-feedback">
                      {registerErrors.email[0]}
                    </div>
                  )}
                </div>
                <div className="mb-3">
                  <label htmlFor="register-password" className="form-label">Contraseña</label>
                  <input 
                    type="password" 
                    className={`form-control ${registerErrors.password ? 'is-invalid' : ''}`}
                    id="register-password"
                    name="password"
                    value={registerData.password}
                    onChange={handleRegisterChange}
                    required
                  />
                  {registerErrors.password && (
                    <div className="invalid-feedback">
                      {registerErrors.password[0]}
                    </div>
                  )}
                </div>
                <div className="mb-3">
                  <label htmlFor="register-password-confirm" className="form-label">Confirmar Contraseña</label>
                  <input 
                    type="password" 
                    className="form-control"
                    id="register-password-confirm"
                    name="password_confirmation"
                    value={registerData.password_confirmation}
                    onChange={handleRegisterChange}
                    required
                  />
                </div>
                {registerErrors.submit && (
                  <div className="alert alert-danger">
                    {registerErrors.submit}
                  </div>
                )}
              </div>
              <div className="modal-footer">
                <button 
                  type="button" 
                  className="btn btn-secondary" 
                  onClick={() => setShowRegisterModal(false)}
                >
                  Cancelar
                </button>
                <button 
                  type="submit" 
                  className="btn btn-primary" 
                  disabled={registerLoading}
                >
                  {registerLoading ? (
                    <>
                      <span className="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                      Registrando...
                    </>
                  ) : (
                    'Registrarse'
                  )}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Login;