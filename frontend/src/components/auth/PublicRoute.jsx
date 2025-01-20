import { Navigate } from 'react-router-dom';

const PublicRoute = ({ children }) => {
  const token = localStorage.getItem('token');
  const user = JSON.parse(localStorage.getItem('user'));

  if (token && user) {
    return <Navigate to="/tasks" replace />;
  }

  return children;
};

export default PublicRoute;