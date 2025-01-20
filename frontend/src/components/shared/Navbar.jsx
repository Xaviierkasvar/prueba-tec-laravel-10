import { useState } from 'react';
import { useNavigate } from 'react-router-dom';

const Navbar = ({ user }) => {
  const [showProfileMenu, setShowProfileMenu] = useState(false);
  const [showProfileModal, setShowProfileModal] = useState(false);
  const navigate = useNavigate();

  const handleLogout = () => {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    navigate('/login');
  };

  const userInitials = user?.name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase();

  return (
    <nav className="navbar navbar-expand-lg navbar-dark bg-primary">
      <div className="container-fluid">
        <a className="navbar-brand" href="#">Sistema de Tareas</a>
        
        <div className="d-flex align-items-center">
          <div className="dropdown">
            <button 
              className="btn btn-primary rounded-circle d-flex align-items-center justify-content-center me-3"
              style={{ width: '40px', height: '40px', backgroundColor: '#0056b3' }}
              onClick={() => setShowProfileMenu(!showProfileMenu)}
            >
              {userInitials}
            </button>
            
            {showProfileMenu && (
              <div className="dropdown-menu show position-absolute" style={{ right: 0 }}>
                <button 
                  className="dropdown-item" 
                  onClick={() => {
                    setShowProfileModal(true);
                    setShowProfileMenu(false);
                  }}
                >
                  Ver Perfil
                </button>
                <div className="dropdown-divider"></div>
                <button className="dropdown-item text-danger" onClick={handleLogout}>
                  Cerrar Sesión
                </button>
              </div>
            )}
          </div>
        </div>
      </div>

      {/* Modal de Perfil */}
      {showProfileModal && (
        <div className="modal show d-block" style={{ backgroundColor: 'rgba(0,0,0,0.5)' }}>
          <div className="modal-dialog">
            <div className="modal-content">
              <div className="modal-header">
                <h5 className="modal-title">Perfil de Usuario</h5>
                <button type="button" className="btn-close" onClick={() => setShowProfileModal(false)}></button>
              </div>
              <div className="modal-body">
                <p><strong>Nombre:</strong> {user?.name}</p>
                <p><strong>Usuario:</strong> {user?.username}</p>
              </div>
              <div className="modal-footer">
                <button type="button" className="btn btn-secondary" onClick={() => setShowProfileModal(false)}>
                  Cerrar
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
    </nav>
  );
};

export default Navbar;