<template>
    <div>
        <!-- Modal Login-->
        <div class="modal fade" id="loginModal" tabindex="-1" role="dialog" aria-labelledby="loginModal" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="loginModal">Entrar</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="container box">
                            <div class="form-group">
                                <label id="emailLabel" for="inputEmailUsername">Email</label>
                                <input
                                    type="text" class="form-control" v-model.trim="user.email"
                                    name="emailUsername" id="inputEmailUsername"
                                    @keyup.enter="login"/>
                            </div>
                            <div class="form-group">
                                <label for="inputPassword">Senha</label>
                                <input
                                    type="password" class="form-control" v-model="user.password"
                                    name="password" id="inputPassword" @keyup.enter="login"/>
                            </div>
                            <a class="btn primary--text" @click="showReset">Esqueceu a sua palavra-passe?</a>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-info" v-on:click.prevent="login" :disabled="!formCompleted">Entrar</button>
                    </div>
                </div>
            </div>
        </div>
        <pedido-nova-password v-on:fechar="fechar()"></pedido-nova-password>
    </div>
</template>

<script type="text/javascript">
import pedidoNovaPassword from './pedidoNovaPassword.vue';

    export default {
        components: {
            'pedido-nova-password': pedidoNovaPassword,
        },
        data: function(){
            return {
                user: {
                    email: "",
                    password:""
                },
            }
        },
        methods: {
            login() {
                if (this.user.email=== "" || this.user.password=== ""){
                    return;
                }
                this.$emit("logging");
                $('#loginModal').modal('hide');
                axios.post('/api/login', this.user).then(response => {
                    this.$store.commit('setToken',response.data.access_token);
                    return axios.get('/api/users/me');
                }).then(response => {
                    this.$store.commit('setUser',response.data.data);
                    this.$emit("logging");
                    this.$emit("logged");
                    this.toastPopUp("success", `Bem Vindo ${this.$store.state.user.nome}`);
                    this.user.email = '';
                    this.user.password = '';
                    this.$socket.emit('user_enter', this.$store.state.user);
                    $('#loginModal').modal('hide');
                }).catch(error =>
                {
                    $('#loginModal').modal('show');
                    this.$emit("logging");
                    this.$store.commit('clearUserAndToken');
                    this.toastErrorApi(error);
                });

            },
            fechar(){
                $('#loginModal').modal('hide');
            },
            showReset(){
                $('#loginModal').modal('hide');
                $('#resetPasswordModal').modal('show');

            },
        },
        computed: {
            formCompleted(){
                return this.user.email && this.user.password;
            }
        },
    }
</script>
