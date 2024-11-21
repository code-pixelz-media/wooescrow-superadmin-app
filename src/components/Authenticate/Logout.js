import React, { useEffect } from 'react';
import { useNavigate } from 'react-router-dom';

const Logout = () => {
  const navigate = useNavigate();

  useEffect(() => {
   
    localStorage.removeItem('isAuthenticated');
        navigate('/', { replace: true });
  }, [navigate]);

  return null; 
};

export default Logout;