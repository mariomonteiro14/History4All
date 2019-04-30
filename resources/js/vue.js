require('./bootstrap');

window.Vue = require('vue');

import VueRouter from 'vue-router';
import Toasted from 'vue-toasted';
import store from './stores/global-store';
import BootstrapVue from 'bootstrap-vue';
import Vuetify from 'vuetify';
import VueCarousel from 'vue-carousel';
import VueAgile from 'vue-agile';
//import VueLoading from 'vue-loading';
import CKEditor from '@ckeditor/ckeditor5-vue';

import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';
import 'vuetify/dist/vuetify.min.css';
//import 'vue-loading/dist/vue-loading.css';

Vue.use(VueRouter);
Vue.use(Toasted);
Vue.use(Vuetify);
Vue.use(VueCarousel);
Vue.use(VueAgile);
Vue.use(BootstrapVue);
Vue.use(CKEditor);

const navbar = Vue.component('navbar', require('./components/widgets/nav.vue').default);
Vue.component('loader', require('vue-spinner/src/MoonLoader.vue').default); //http://greyby.github.io/vue-spinner/?ref=madewithvuejs.com

const routes = [
    { path: '/', name: 'index',component: require('./components/index.vue').default},
    { path: '/me/perfil', name: 'perfil',component: require('./components/users/perfil.vue').default},
    { path: '/patrimonios', name: 'patrimonios',component: require('./components/patrimonio/patrimonios.vue').default},
    { path: '/patrimonio/:id', name: 'patrimoniosShow',component: require('./components/patrimonio/showPatrimonio.vue').default, props: true},
    { path: '/admin', name: 'dashboard',component: require('./components/dashboard.vue').default},
    { path: '/admin/patrimonios', name: 'gerirPatrimonios',component: require('./components/patrimonio/gerirPatrimonios.vue').default},
    { path: '/admin/patrimonios/editar', name: 'editarPatrimonio',component: require('./components/patrimonio/editarPatrimonio.vue').default},
    { path: '/admin/escolas', name: 'gerirEscolas',component: require('./components/escola/gerirEscolas.vue').default},
    { path: '/atividades', name: 'atividades',component: require('./components/atividades/atividades.vue').default},
    { path: '/admin/users', name: 'gestor_users',component: require('./components/users/users.vue').default},
    { path: '/users/registarPassword/:token', name: 'registarPassword',component: require('./components/users/registarPassword.vue').default, props: true},
    { path: '/escola/turmas', name: 'turmas',component: require('./components/escola/turmas.vue').default},

];

const router = new VueRouter({
    mode: 'history',
    routes:routes
});

router.beforeEach((to, from, next) => {
    if(!store.state.user){
        store.commit('loadTokenAndUserFromSession');
    }
    let user = store.state.user;
    if((to.name == 'dashboard') || (to.name == 'gerirPatrimonios') || (to.name == 'editarPatrimonio') ||
        (to.name == 'gerirEscolas') || (to.name == 'gestor_users') || (to.name == 'criarPatrimonio')){
        if(!user || user.tipo !== "admin"){
            next("/");
            return;
        }
    }
    if(to.name == 'atividades'){
        if(!user || user.tipo == "admin"){//professor e aluno
            next("/");
            return;
        }
    }
    if(to.name == 'perfil'){
        if(!user){
            next("/");
            return;
        }
    }
    next();
});

var common = {
    methods: {
        toastPopUp(type, msg){
            Vue.toasted.clear();
            if(type == "show"){
                Vue.toasted.show(msg, {
                    position: "bottom-center",
                    duration: 5000,
                });
                return;
            }
            if(type == "error"){
                Vue.toasted.error(msg, {
                    position: "bottom-center",
                    duration: 10000,
                });
                return;
            }
            if(type == "success"){
                Vue.toasted.success(msg, {
                    position: "bottom-center",
                    duration: 5000,
                });
                return;
            }
        },
        getUserPhoto(url){
            return "/storage/profiles/" + url;
        },
        getPatrimonioPhoto(url){
            return "/storage/patrimonios/" + url;
        },
    },
    computed:{
        getAuthUser: function(){
            return this.$store.state.user;
        },
    },
};

Vue.mixin(common);

const app = new Vue({
    router,
    store,
    created() {
        this.$store.commit('loadTokenAndUserFromSession');
    },
}).$mount('#app');
