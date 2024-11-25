import React from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { IconLayoutDashboard , IconAperture, IconArticle , IconAlertCircle , IconLogin } from '@tabler/icons-react';
import WooescrowLogo from '../../Assets/images/logos/wooescrow-logo.svg';

const Menu = () => {
	const navigate = useNavigate();

	const handleLogout = () => {
		navigate('/logout');
	};

    return (
        <aside className="left-sidebar">
            <div>
                <div className="brand-logo d-flex align-items-center justify-content-between">
                    <Link
                        to="/"
                        className="text-nowrap logo-img d-flex justify-content-center"
                    >
                       <WooescrowLogo />
                        <h2 className="wooescrow-title ms-2">Wooescrow</h2>
                    </Link>
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
                            <Link className="sidebar-link" to="/" aria-expanded="false">
                                <span>
                                    <IconLayoutDashboard  />
                                </span>
                                <span className="hide-menu">Dashboard</span>
                            </Link>
                        </li>

                        <li className="sidebar-item">
                            <Link
                                className="sidebar-link"
                                to="/sites"
                                aria-expanded="false"
                            >
                                <span>
                                   <IconAperture />
                                </span>
                                <span className="hide-menu">Sites Page</span>
                            </Link>
                        </li>
                        
                        <li className="sidebar-item">
                            <Link className="sidebar-link" to="/users" aria-expanded="false">
                                <span>
                                   <IconArticle />
                                </span>
                                <span className="hide-menu">Users</span>
                            </Link>
                        </li>

                        <li className="sidebar-item">
                            <Link className="sidebar-link" to="/user-detail">
                                <span>
                                  <IconAlertCircle />
                                </span>
                                <span className="hide-menu">User Detail page</span>
                            </Link>
                        </li>

                        <li className="sidebar-item">
                            <Link className="sidebar-link" to="/requests" aria-expanded="false">
                                <span>
                                  <IconAlertCircle />
                                </span>
                                <span className="hide-menu">Requests</span>
                            </Link>
                        </li>
                        
                        <li className="sidebar-item">
                            <Link
                                className="sidebar-link"
                                to="/transactions"
                                aria-expanded="false"
                            >
                                <span>
                                  <IconAlertCircle />
                                </span>
                                <span className="hide-menu">Transactions</span>
                            </Link>
                        </li>
                        <li className="sidebar-item">
                            <Link
                                className="sidebar-link"
                                to="/test"
                                aria-expanded="false"
                            >
                                <span>
                                  <IconAlertCircle />
                                </span>
                                <span className="hide-menu">Test</span>
                            </Link>
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