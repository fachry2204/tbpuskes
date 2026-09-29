import { createApp, h } from 'vue'
import AdminLayout from './AdminLayout.vue'
import './style.css'
import './upload-ui.css'
import MainLogin from './MainLogin.vue'
import AdminDashboard from './AdminDashboard.vue'
import AdminLogin from './AdminLogin.vue'
import AdminPatients from './AdminPatients.vue'
import AdminPatientCreate from './AdminPatientCreate.vue'
import AdminPatientHistory from './AdminPatientHistory.vue'
import AdminCadres from './AdminCadres.vue'
import AdminCadreCreate from './AdminCadreCreate.vue'
import AdminTreatmentPlaces from './AdminTreatmentPlaces.vue'
import AdminMedications from './AdminMedications.vue'
import AdminSchedules from './AdminSchedules.vue'
import AdminControlSchedule from './AdminControlSchedule.vue'
import AdminUsers from './AdminUsers.vue'
import RoleDashboard from './RoleDashboard.vue'
import AdminMonitoring from './AdminMonitoring.vue'
import AdminStatistics from './AdminStatistics.vue'

if (location.pathname === '/admin/medication-plans') history.replaceState(null, '', '/admin/patients')
if (location.pathname === '/admin/control-schedules' || location.pathname === '/admin/schedules') history.replaceState(null, '', '/admin/schedules')
const path = window.location.pathname
const isAdminPath = path.startsWith('/admin'), isPortal=path.startsWith('/app')||path.startsWith('/kader')
const page = isPortal?(sessionStorage.getItem('tb_token')?RoleDashboard:MainLogin):isAdminPath ? (sessionStorage.getItem('tb_token') ? (path.startsWith('/admin/monitoring') ? AdminMonitoring : path.startsWith('/admin/statistics') ? AdminStatistics : path.startsWith('/admin/users') ? AdminUsers : path === '/admin/schedules/create' ? AdminControlSchedule : path.startsWith('/admin/schedules') ? AdminSchedules : path.startsWith('/admin/medications') ? AdminMedications : path.startsWith('/admin/treatment-places') ? AdminTreatmentPlaces : /^\/admin\/patients\/\d+\/history$/.test(path) ? AdminPatientHistory : path.startsWith('/admin/patients/create') ? AdminPatientCreate : path.startsWith('/admin/patients') ? AdminPatients : path.startsWith('/admin/cadres/create') ? AdminCadreCreate : path.startsWith('/admin/cadres') ? AdminCadres : AdminDashboard) : AdminLogin) : MainLogin
createApp(isAdminPath && sessionStorage.getItem('tb_token') ? { render: () => h(AdminLayout, null, { default: () => h(page) }) } : page).mount('#app')
