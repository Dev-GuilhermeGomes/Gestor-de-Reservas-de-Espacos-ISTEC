import { createRoot } from 'react-dom/client';
import App from './src/App.jsx';

const rootElement = document.getElementById('app');

if (rootElement) {
    createRoot(rootElement).render(<App initialPage={window.__APP_PAGE__} />);
}
