import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import WooescrowLogo from '../../Assets/images/logos/wooescrow-logo.svg';

const Login = () => {
	const [isLoading, setIsLoading] = useState(true);
	const [username, setUsername] = useState('');
	const [password, setPassword] = useState('');
	const [error, setError] = useState('');
	const navigate = useNavigate();

	useEffect(() => {

		const isAuthenticated = localStorage.getItem('isAuthenticated');

		if (isAuthenticated) {

			navigate('/dashboard');
		} else {

			setIsLoading(false);
		}
	}, [navigate]);

	const handleSubmit = (e) => {
		e.preventDefault();

		if (username === 'admin' && password === 'password') {
			localStorage.setItem('isAuthenticated', 'true');
			navigate('/dashboard');
		} else {
			setError('Invalid username or password');
		}
	};

	if (isLoading) {

		return <div className="position-relative overflow-hidden radial-gradient min-vh-100 d-flex align-items-center justify-content-center">Loading...</div>;
	}


	return (
		<div className="position-relative overflow-hidden radial-gradient min-vh-100 d-flex align-items-center justify-content-center">
			<div className="d-flex align-items-center justify-content-center w-100">
				<div className="row justify-content-center w-100">
					<div className="col-md-8 col-lg-6 col-xxl-4">
						<div className="card mb-0">
							<div class="card-body">
								<a href="./index.html" class="text-nowrap logo-img text-center d-block py-3 w-100 d-flex justify-content-center">
									<WooescrowLogo />
									<h1 className="wooescrow-title ms-2">Wooescrow</h1>
								</a>
								<form onSubmit={handleSubmit}>
									<div className='mb-3'>
										<label for="user-name-input" class="form-label"
										>Username</label
										>
										<input
											type="text"
											className='form-control'
											id="user-name-input"
											value={username}
											onChange={(e) => setUsername(e.target.value)}
										/>
									</div>
									<div className='mb-4'>
										<label for="passwordInput" class="form-label"
										>Password</label
										>
										<input
											type="password"
											className='form-control'
											id='passwordInput'
											value={password}
											onChange={(e) => setPassword(e.target.value)}
										/>
									</div>
									{error && <p style={{ color: 'red' }}>{error}</p>}
									<button className='btn btn-primary w-100 py-8 fs-4 mb-4 rounded-2' type="submit">Login</button>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	);
};

export default Login;