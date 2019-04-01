require('./bootstrap');

window.Vue = require('vue');

import VueRouter from 'vue-router';
import Toasted from 'vue-toasted';
import store from './stores/global-store';
import BootstrapVue from 'bootstrap-vue';
import Vuetify from 'vuetify';
import VueCarousel from 'vue-carousel';
import VueAgile from 'vue-agile';
import VueLoading from 'vue-loading';


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

const navbar = Vue.component('navbar', require('./components/widgets/nav.vue').default);
Vue.component('loader', require('vue-spinner/src/MoonLoader.vue').default); //http://greyby.github.io/vue-spinner/?ref=madewithvuejs.com

const routes = [
    { path: '/', name: 'index',component: require('./components/index.vue').default},
    { path: '/patrimonios', name: 'patrimonios',component: require('./components/patrimonios.vue').default},
    { path: '/patrimonio/:id', name: 'patrimoniosShow',component: require('./components/showPatrimonio.vue').default, props: true},
    { path: '/dashboard', name: 'dashboard',component: require('./components/dashboard.vue').default},
    { path: '/patrimonios/gerir', name: 'patrimoniosGerir',component: require('./components/gerirPatrimonios.vue').default},
];

const router = new VueRouter({
    routes:routes
});


var common = {
    methods: {
        toastPopUp(type, msg){
            Vue.toasted.clear();
            if(type == "show"){
                Vue.toasted.show(msg, {
                    position: "bottom-center",
                    duration: 3000,
                });
                return;
            }
            if(type == "error"){
                Vue.toasted.error(msg, {
                    position: "bottom-center",
                    duration: 3000,
                });
                return;
            }
            if(type == "success"){
                Vue.toasted.success(msg, {
                    position: "bottom-center",
                    duration: 3000,
                });
                return;
            }
        },
        getUserPhoto(url){
            return "storage/profiles/" + url;
        },
        getPatrimonioPhoto(url){
            return "storage/patrimonios/" + url;
        },
    },
    computed:{
        getAuthUser: function(){
            console.log(this.$store.state.user);
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
