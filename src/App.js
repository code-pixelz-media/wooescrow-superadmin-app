import React from 'react';
import 'bootstrap/dist/css/bootstrap.min.css';
import './Assets/css/styles.min.css';
import './Assets/css/wooescrow-superadmin.css';
import { BrowserRouter as Router, Routes, Route  } from 'react-router-dom';
import Login from './components/Authenticate/Login';
import Dashboard from './components/Dashboard/Dashboard';
import Logout from './components/Authenticate/Logout';
import ProtectedRoute from './components/Libs/ProtectedRoute';
import NotFound from './components/Libs/NotFound';
import Sites from './components/Dashboard/Contents/Sites';
import Test from './components/Dashboard/Contents/Test';

const App = () => {
  return (
    <Router>
      <Routes>
        <Route path="/" element={<Login />} />
        <Route
          path="/dashboard"
          element={
            <ProtectedRoute>
              <Dashboard />
            </ProtectedRoute>
            
          }
        />
        <Route path="/logout" element={<Logout />} />
        <Route path="/sites" element={<Sites />} />
        <Route path="/test" element={<Test/>} />
        <Route path="*" element={<NotFound />} />
      </Routes>
    </Router>

  );
};

export default App;