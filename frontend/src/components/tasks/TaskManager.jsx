import React, { useState, useEffect } from 'react';
import Navbar from '../shared/Navbar';
import api from '../../services/api';

const TaskManager = () => {
  const [tasks, setTasks] = useState([]);
  const [currentPage, setCurrentPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [loading, setLoading] = useState(false);
  const [showModal, setShowModal] = useState(false);
  const [currentTask, setCurrentTask] = useState(null);
  const [formData, setFormData] = useState({
    title: '',
    description: ''
  });
  const user = JSON.parse(localStorage.getItem('user'));

  const fetchTasks = async (page = 1) => {
    setLoading(true);
    try {
      const response = await api.get(`/tasks?page=${page}`);
      setTasks(response.data.data);
      setTotalPages(Math.ceil(response.data.total / response.data.per_page));
      setCurrentPage(page);
    } catch (error) {
      console.error('Error al cargar tareas:', error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchTasks();
  }, []);

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      if (currentTask) {
        await api.put(`/tasks/${currentTask.id}`, formData);
      } else {
        await api.post('/tasks', formData);
      }
      setShowModal(false);
      setFormData({ title: '', description: '' });
      setCurrentTask(null);
      fetchTasks(currentPage);
    } catch (error) {
      console.error('Error al guardar tarea:', error);
    }
  };

  const handleEdit = (task) => {
    setCurrentTask(task);
    setFormData({
      title: task.title,
      description: task.description
    });
    setShowModal(true);
  };

  const handleDelete = async (id) => {
    if (window.confirm('¿Está seguro de eliminar esta tarea?')) {
      try {
        await api.delete(`/tasks/${id}`);
        fetchTasks(currentPage);
      } catch (error) {
        console.error('Error al eliminar tarea:', error);
      }
    }
  };

  const handleToggleStatus = async (id) => {
    try {
      await api.put(`/tasks/${id}/toggle-status`);
      fetchTasks(currentPage);
    } catch (error) {
      console.error('Error al cambiar estado:', error);
    }
  };

  const getStatusBadgeClass = (status) => {
    return status === 'active' ? 'bg-success' : 'bg-danger';
  };

  return (
    <div className="min-vh-100 bg-light">
      <Navbar user={user} />
      
      <div className="container py-4">
        <div className="d-flex justify-content-between align-items-center mb-4">
          <h2>Gestión de Tareas</h2>
          <button 
            className="btn btn-primary"
            onClick={() => {
              setCurrentTask(null);
              setFormData({ title: '', description: '' });
              setShowModal(true);
            }}
          >
            Nueva Tarea
          </button>
        </div>

        {loading ? (
          <div className="text-center py-5">
            <div className="spinner-border text-primary" role="status">
              <span className="visually-hidden">Cargando...</span>
            </div>
          </div>
        ) : (
          <>
            <div className="table-responsive">
              <table className="table table-hover">
                <thead className="table-light">
                  <tr>
                    <th>Título</th>
                    <th>Descripción</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  {tasks.map(task => (
                    <tr key={task.id}>
                      <td>{task.title}</td>
                      <td>{task.description}</td>
                      <td>
                        <span className={`badge ${getStatusBadgeClass(task.status)}`}>
                          {task.status === 'active' ? 'Activo' : 'Inactivo'}
                        </span>
                      </td>
                      <td>
                        <div className="btn-group">
                          <button 
                            className="btn btn-sm btn-outline-primary"
                            onClick={() => handleEdit(task)}
                          >
                            Editar
                          </button>
                          <button 
                            className="btn btn-sm btn-outline-secondary"
                            onClick={() => handleToggleStatus(task.id)}
                          >
                            {task.status === 'active' ? 'Desactivar' : 'Activar'}
                          </button>
                          <button 
                            className="btn btn-sm btn-outline-danger"
                            onClick={() => handleDelete(task.id)}
                          >
                            Eliminar
                          </button>
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>

            {/* Paginación */}
            {totalPages > 1 && (
              <nav className="d-flex justify-content-center mt-4">
                <ul className="pagination">
                  {[...Array(totalPages)].map((_, i) => (
                    <li 
                      key={i + 1} 
                      className={`page-item ${currentPage === i + 1 ? 'active' : ''}`}
                    >
                      <button 
                        className="page-link"
                        onClick={() => fetchTasks(i + 1)}
                      >
                        {i + 1}
                      </button>
                    </li>
                  ))}
                </ul>
              </nav>
            )}
          </>
        )}
      </div>

      {/* Modal para Crear/Editar */}
      {showModal && (
        <div className="modal show d-block" style={{ backgroundColor: 'rgba(0,0,0,0.5)' }}>
          <div className="modal-dialog">
            <div className="modal-content">
              <div className="modal-header">
                <h5 className="modal-title">
                  {currentTask ? 'Editar Tarea' : 'Nueva Tarea'}
                </h5>
                <button 
                  type="button" 
                  className="btn-close"
                  onClick={() => setShowModal(false)}
                ></button>
              </div>
              <form onSubmit={handleSubmit}>
                <div className="modal-body">
                  <div className="mb-3">
                    <label className="form-label">Título</label>
                    <input
                      type="text"
                      className="form-control"
                      value={formData.title}
                      onChange={(e) => setFormData({...formData, title: e.target.value})}
                      required
                    />
                  </div>
                  <div className="mb-3">
                    <label className="form-label">Descripción</label>
                    <textarea
                      className="form-control"
                      rows="3"
                      value={formData.description}
                      onChange={(e) => setFormData({...formData, description: e.target.value})}
                    ></textarea>
                  </div>
                </div>
                <div className="modal-footer">
                  <button 
                    type="button" 
                    className="btn btn-secondary"
                    onClick={() => setShowModal(false)}
                  >
                    Cancelar
                  </button>
                  <button type="submit" className="btn btn-primary">
                    {currentTask ? 'Actualizar' : 'Crear'}
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default TaskManager;