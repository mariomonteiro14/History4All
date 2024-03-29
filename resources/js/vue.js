require('./bootstrap');

window.Vue = require('vue');

import VueRouter from 'vue-router';
import Toasted from 'vue-toasted';
import store from './stores/global-store';
import BootstrapVue from 'bootstrap-vue';
import Vuetify from 'vuetify';
import VueCarousel from 'vue-carousel';
import VueAgile from 'vue-agile';
import VueSocketio from 'vue-socket.io';
//import VueLoading from 'vue-loading';
import CKEditor from '@ckeditor/ckeditor5-vue';
import SocialSharing from 'vue-social-sharing';
import VueCookies from 'vue-cookies';
import VueScrollTo from 'vue-scrollto';

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
Vue.use(SocialSharing);
Vue.use(VueCookies);
Vue.use(VueScrollTo);

const navbar = Vue.component('navbarc', require('./components/widgets/nav.vue').default);
const footer = Vue.component('footerc', require('./components/widgets/foter.vue').default);

Vue.use(new VueSocketio({
    debug: true,
    connection: 'http://142.93.219.146:8080' //TODO
}));

Vue.component('loader', require('vue-spinner/src/MoonLoader.vue').default); //http://greyby.github.io/vue-spinner/?ref=madewithvuejs.com

const routes = [
    { path: '/', name: 'index',component: require('./components/index.vue').default},
    { path: '/me/perfil', name: 'perfil',component: require('./components/users/perfil.vue').default},
    { path: '/users/:id', name: 'perfilUser',component: require('./components/users/perfil.vue').default,props: true},
    { path: '/patrimonios', name: 'patrimonios',component: require('./components/patrimonio/patrimonios.vue').default},
    { path: '/patrimonio/:id', name: 'patrimoniosShow',component: require('./components/patrimonio/showPatrimonio.vue').default, props: true},
    { path: '/admin/patrimonios', name: 'gerirPatrimonios',component: require('./components/patrimonio/gerirPatrimonios.vue').default},
    { path: '/admin/escolas', name: 'gerirEscolas',component: require('./components/escola/gerirEscolas.vue').default},
    { path: '/atividades', name: 'atividades',component: require('./components/atividades/atividades.vue').default},
    { path: '/atividade/:id', name: 'atividadesShow',component: require('./components/atividades/showAtividade.vue').default, props: true},
    { path: '/admin/users', name: 'gestorUsers',component: require('./components/users/users.vue').default},
    { path: '/users/registarPassword/:token', name: 'registarPassword',component: require('./components/users/registarPassword.vue').default, props: true},
    { path: '/users/resetPassword/:token', name: 'resetPassword',component: require('./components/users/resetPassword.vue').default, props: true},
    //{ path: '/escola/turmas', name: 'turmas',component: require('./components/escola/turmas.vue').default},
    { path: '/users/alterarEmail/:token/:email', name: 'alterarEmail',component: require('./components/users/alterarEmail.vue').default,props: true},
    //{ path: '/professores/chat', name: 'chatProfessores',component: require('./components/widgets/chatProfessores.vue').default},
    { path: '/professores/gestor/:tab', name: 'professorGestor',component: require('./components/users/professorGestor.vue').default, props: true},
    { path: '/alunos/escola', name: 'alunosGestor',component: require('./components/users/alunoGestor.vue').default, props: true},
    { path: '/forums', name: 'forums',component: require('./components/forum/forums.vue').default},
    { path: '/forums/:id', name: 'showForum',component: require('./components/forum/showForum.vue').default, props: true},
    { path: '/*', name: 'unknown'},

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
    if(to.name == 'gerirPatrimonios' || to.name == 'gerirEscolas' || to.name == 'gestorUsers'){
        if(!user || user.tipo !== "admin"){
            next("/");
            return;
        }
    }
    if(to.name == 'turmas' || to.name == 'gerirAtividades' || to.name == 'chatProfessores' || to.name =='professorGestor'){
        if(!user || user.tipo !== "professor"){
            next("/");
            return;
        }
    }
    if(to.name == 'perfil' || to.name == 'atividades' || to.name == 'atividadesShow' || to.nome == 'perfilUser'){
        if(!user){
            next("/");
            return;
        }
    }

    if(to.name == 'alunosGestor'){
        if(!user || user.tipo !== "aluno"){
            next("/");
            return;
        }
    }

    if(to.name == 'unknown') {
        next("/");
        return;
    }
    next();
});

var common = {
    data: function () {
        return {
            colorDefault: 'light-green darken-1'
        }
    },
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
        toastErrorApi(error){
            this.toastPopUp("error", error.response.data.errors ?
                `${error.response.data.errors[Object.keys(error.response.data.errors)[0]]}` : `${error.response.data.message}`);
        },
        validarInputComboBox(selected){//impede que se insira através do teclado
            if (selected.length === 0){
                return selected;
            }
            if (typeof selected[selected.length - 1] == "string"){
                selected.pop();
            }
            return selected;
        },
        getUserPhoto(url){
            return "/storage/profiles/" + url;
        },
        getPrimeiroUltimoNome(nome){
            let array = nome.split(' ');
            if (array.length == 1){
                return nome;
            }
            return array[0] + " " + array.pop();
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
    sockets: {
        connect(){
            if(this.$store.state.user !== null){
                this.$socket.emit('user_enter', store.state.user);
            }
        },
    },
    created() {
        this.$store.commit('loadTokenAndUserFromSession');
    },
}).$mount('#app');
