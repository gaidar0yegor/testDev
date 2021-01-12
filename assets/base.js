// Entrypoint for all assets of the application, anonymous or loggedin

import '../node_modules/font-awesome/css/font-awesome.css';
import '../node_modules/bootstrap/dist/css/bootstrap.css';
import './styles/footer.css';

import './js/matomo';

// Import all images so that we can use them in twig templates
const imagesContext = require.context('./img', true);
imagesContext.keys().forEach(imagesContext);
