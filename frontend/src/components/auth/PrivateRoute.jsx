import { Navigate } from 'react-router-dom';

const PrivateRoute = ({ children }) => {
  const token = localStorage.getItem('token');
  const user = JSON.parse(localStorage.getItem('user'));

  // Si no hay token o usuario, redirige al login
  if (!token || !user) {
    return <Navigate to="/login" replace />;
  }

  // Si hay token y usuario, renderiza el componente hijo
  return children;
};

export default PrivateRoute;