<template>
    <div>
        <br><br>
        <div class="container py-5" v-if="this.user.id">
            <div class="row">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-6 mx-auto">
                            <div class="card rounded-0">
                                <div class="card-header">
                                    <h3 class="mb-0">Confirmar alteração de email</h3>
                                </div>
                                <div class="card-body">
                                    <form class="form" role="form" autocomplete="off" id="formLogin" novalidate=""
                                          method="POST">
                                        <p>O seu novo email está confirmado: {{emailCompleto}}</p>
                                        <v-btn :color="defaultColor" flat @click="$router.push('/')">Ok</v-btn>

                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <br><br><br>
    </div>
</template>

<script>
    export default {
        props: ['token', 'email'],
        data: function () {
            return {
                user: {},
            };
        },
        created() {
            if (this.$store.state.user) {
                this.logout();
            }
            this.getUser();

        },
        methods: {
            alterarEmail: function () {
                axios.put("/api/register/novoEmail/" + this.user.id, {'email': this.emailCompleto}).then(response => {

                }).catch(errors => {
                    this.toastErrorApi(error);
                });
            },
            logout() {
                axios.get('/api/logout').then(response => {
                    this.$store.commit('clearUserAndToken');
                    this.$socket.emit('user_exit', this.$store.state.user);
                }).catch(error => {
                    this.$store.commit('clearUserAndToken');
                    this.toastErrorApi(error);
                });
            },
            getUser: function () {
                axios.get("/api/users/token/" + this.token).then(response => {
                    if (response.data === "" && response.data.email_verified_at === null) {//token inválido
                        this.$router.push({name: 'index'});
                    }
                    if (response.data.id) {
                        this.user = response.data;
                        this.alterarEmail();
                    }else {
                        this.toastPopUp("error", "Operação invalida");
                        this.$router.push('/');
                    }

                }).catch(errors => {
                    console.log(errors);
                });
            },
        },
        computed: {
            emailCompleto: function () {
                return this.email + '@' + window.location.search.substr(1);
            }
        }
    }
</script>
