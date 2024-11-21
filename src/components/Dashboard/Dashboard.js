import React, { Fragment } from 'react';
import Menu from './Menu';
import Header from './Header';
import Footer from './Footer';


const Dashboard = () => {
	return (
		<Fragment>
			<Menu />
			<div className='body-wrapper'>
				<Header />
				<Footer />
			</div>
		</Fragment>
	);
};

export default Dashboard;