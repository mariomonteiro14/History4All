<template>
    <div>
        <!-- Modal Login-->
        <div class="modal fade" id="loginModal" tabindex="-1" role="dialog" aria-labelledby="loginModal" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="loginModal">Login</h5>
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
                                    placeholder="Email address"/>
                            </div>
                            <div class="form-group">
                                <label for="inputPassword">Password</label>
                                <input
                                    type="password" class="form-control" v-model="user.password"
                                    name="password" id="inputPassword"/>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <v-btn class="btn btn-info" data-toggle="modal" data-target="#resetPasswordModal">Reset da password</v-btn>
                        <button class="btn btn-info" v-on:click.prevent="login" :disabled="!formCompleted">Login</button>
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
                this.$emit("logging");
                axios.post('/api/login', this.user).then(response => {
                    this.$store.commit('setToken',response.data.access_token);
                    return axios.get('/api/users/me');
                }).then(response => {
                    this.$emit("logging");
                    this.$store.commit('setUser',response.data.data);
                    this.toastPopUp("success", `Bem Vindo ${this.$store.state.user.nome}`);
                    this.user.email = '';
                    this.user.password = '';
                    this.$socket.emit('user_enter', this.$store.state.user);
                    $('#loginModal').modal('hide');
                    this.$router.push({name: 'index'});
                }).catch(error => {
                    this.$emit("logging");
                    this.$store.commit('clearUserAndToken');
                    if(error.response.status && error.response.status === 401){
                        this.toastPopUp("error", `${error.response.data.message}`);
                    }else{
                        this.toastPopUp("error", `${error.response.data.message}`);
                    }
                });
                $('#loginModal').modal('hide');
            },
            fechar(){
                $('#loginModal').modal('hide');
            }
        },
        computed: {
            formCompleted(){
                return this.user.email && this.user.password;
            }
        },
    }
</script>
