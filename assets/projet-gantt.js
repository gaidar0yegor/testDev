import 'dhtmlx-gantt/codebase/dhtmlxgantt.css';
import './js/dhtmlx-gantt/style.css';
import './js/dhtmlx-gantt/affectation-equipe';
import 'dhtmlx-gantt/codebase/dhtmlxgantt';
import './js/dhtmlx-gantt/export_api';
import './js/dhtmlx-gantt/projet-planning';
import './js/dhtmlx-gantt/assign-users';
import './js/dhtmlx-gantt/nav-tab';
import './js/projet-admin-charts/planning-tasks-statuts';

// Import all icons
const imagesContext = require.context('./js/dhtmlx-gantt/icons', true);
imagesContext.keys().forEach(imagesContext);