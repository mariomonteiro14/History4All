<template>
    <div>
        <nav class="nav nav-tabs">
            <li><router-link to="/" class="nav-item nav-link"><h3>History4All</h3></router-link></li>
            <li><router-link to="/patrimonios" class="btn btn-primary navbar-btn">Patrimónios</router-link></li>
            <li><router-link to="/createPatrimonio" class="btn btn-primary navbar-btn">Criar Patrimónios</router-link></li>
            <li><router-link to="/editPatrimonio" class="btn btn-primary navbar-btn">Editar Patrimónios</router-link></li>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item" v-if="!this.$store.state.user">
                    <button type="button" class="btn btn-info navbar-btn" data-toggle="modal" data-target="#loginModal">
                        Login
                    </button>
                </li>
                <li class="nav-item" v-else>
                    <button type="button" class="btn btn-info navbar-btn" @click="logout">
                        Logout
                    </button>
                </li>
            </ul>
        </nav>

        <!-- Login Modal -->
        <login></login>

    </div>
</template>

<style>
    .material-icons.md-18 { font-size: 16px; }
    .navbar-nav{
        -webkit-flex-direction: row;
        -ms-flex-direction: row;
        flex-direction: row;
    }
    .h4 span{
        margin-right: 5px;
    }
    .btn-static {
        background-color: white;
        border: none;
    }
    .btn-static:active {
        -moz-box-shadow:    inset 0 0 0px white;
        -webkit-box-shadow: inset 0 0 0px white;
        box-shadow:         inset 0 0 0px white;
    }
    .navbar-nav > span{
        padding-right: 30px;
    }
    .navbar-nav > button{
        padding-right: 20px;
    }
</style>

<script type="text/javascript">
    import login from './login.vue';

    export default {
        components: {
            'login' : login,
        },
        data: function(){
            return {
            }
        },
        methods: {
            logout() {
                axios.get('api/logout').then(response => {
                    this.$store.commit('clearUserAndToken');
                    this.toastPopUp("success", "Logged out");
                    this.$router.push({name: 'index'});
                }).catch(error => {
                    this.$store.commit('clearUserAndToken');
                    this.toastPopUp("error", "Error on logout");
                    console.log(error);
                })
            },
        },

        computed: {
        },
    }
</script>
