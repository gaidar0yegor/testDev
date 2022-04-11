import 'dhtmlx-gantt/codebase/dhtmlxgantt.css';
import './js/dhtmlx-gantt/style.css';
import 'dhtmlx-gantt/codebase/dhtmlxgantt';
import './js/dhtmlx-gantt/export_api';
import './js/dhtmlx-gantt/projet-planning';
import './js/dhtmlx-gantt/assign-users';

// Import all icons
const imagesContext = require.context('./js/dhtmlx-gantt/icons', true);
imagesContext.keys().forEach(imagesContext);