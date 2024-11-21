import React from 'react';
import { useNavigate } from 'react-router-dom';
import { IconLayoutDashboard , IconAperture, IconArticle , IconAlertCircle , IconLogin, icons } from '@tabler/icons-react';
import WooescrowLogo from '../../Assets/images/logos/wooescrow-logo.svg';
import Dashboard from './Dashboard';




const Menu = () => {
	const navigate = useNavigate();
	
	const navMenus = {
	    dashboard : {
	        'icon' : <IconLayoutDashboard/>,
	        'title': 'Dashboard'
	    },
	    
	    sites : {
	    
	    },
	    
	    users : {
	    
	    },
	    
	    requests : {
	    
	    },
	    
	    transactions : {
	    
	    },
	    
	    logout : {
	    
	    
	    }
	};

	const handleLogout = () => {
		navigate('/logout');
	};
    return (
        <aside className="left-sidebar">
            <div>
                <div className="brand-logo d-flex align-items-center justify-content-between">
                    <a
                        href="index.html"
                        className="text-nowrap logo-img d-flex justify-content-center"
                    >
                       <WooescrowLogo />
                        <h2 className="wooescrow-title ms-2">Wooescrow</h2>
                    </a>
                    <div
                        className="close-btn d-xl-none d-block sidebartoggler cursor-pointer"
                        id="sidebarCollapse"
                    >
                        <i className="ti ti-x fs-8"></i>
                    </div>
                </div>

                <nav className="sidebar-nav scroll-sidebar" data-simplebar="">
                    <ul id="sidebarnav">

                        <li className="sidebar-item">
                            <a className="sidebar-link active" href="./index.html" aria-expanded="false">
                                <span>
                                    <IconLayoutDashboard  />
                                </span>
                                <span className="hide-menu">Dashboard</span>
                            </a>
                        </li>

                        <li className="sidebar-item">
                            <a
                                className="sidebar-link"
                                href="./sites-page.html"
                                aria-expanded="false"
                            >
                                <span>
                                   <IconAperture />
                                </span>
                                <span className="hide-menu">Sites Page</span>
                            </a>
                        </li>
                        <li className="sidebar-item">
                            <a className="sidebar-link" href="./users.html" aria-expanded="false">
                                <span>
                                   <IconArticle />
                                </span>
                                <span className="hide-menu">Users</span>
                            </a>
                        </li>
                        <li className="sidebar-item">
                            <a className="sidebar-link" href="./user-detail.html">
                                <span>
                                  <IconAlertCircle />
                                </span>
                                <span className="hide-menu">User Detail page</span>
                            </a>
                        </li>
                        <li className="sidebar-item">
                            <a className="sidebar-link" href="./requests.html" aria-expanded="false">
                                <span>
                                  <IconAlertCircle />
                                </span>
                                <span className="hide-menu">Requests</span>
                            </a>
                        </li>
                        <li className="sidebar-item">
                            <a
                                className="sidebar-link"
                                href="./transactions.html"
                                aria-expanded="false"
                            >
                                <span>
                                  <IconAlertCircle />
                                </span>
                                <span className="hide-menu">Transactions</span></a
                            >
                        </li>

                        <li className="sidebar-item">
                            <a
                                className="sidebar-link"
                                aria-expanded="false"
                                style={{cursor:'pointer'}}
                                onClick={handleLogout}
                            >
                                <span>
                                   <IconLogin />
                                </span>
                                <span className="hide-menu">Logout</span>
                            </a>
                        </li>
                    </ul>
                </nav>

            </div>

        </aside>
    )
};


export default Menu;