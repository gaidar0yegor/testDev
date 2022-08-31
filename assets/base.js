// Entrypoint for all assets of the application, anonymous or loggedin

import '../node_modules/bootstrap/dist/css/bootstrap.css';
import '../node_modules/font-awesome/css/font-awesome.css';
import './styles/footer.css';

import './js/toggle-password'
import './js/popper';
import './js/matomo';
import './js/toastr';

// Import all images so that we can use them in twig templates
const imagesContext = require.context('./img', true);
imagesContext.keys().forEach(imagesContext);
