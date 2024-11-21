import React from "react";
import { createRoot } from 'react-dom/client';
import App from "./App";

const rootElement = document.getElementById('main-wrapper');

const root = createRoot(rootElement);
root.render(
    <App />
);