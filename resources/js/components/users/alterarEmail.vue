<template>
    <div>
        <br><br>
        <div class="container py-5">
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
                                        <p>Tem a certeza que pretende alterar o seu email para {{emailCompleto}}?</p>
                                        <button type="submit" class="btn btn-success btn-lg float-right" id="btnLogin"
                                                v-on:click.prevent="alterarEmail" 
                                                :disabled='!this.$store.state.user || this.$store.state.user.id != this.id'>
                                            Confirmar alteração de email
                                        </button>
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
        props: ['id', 'email'],
        data: function () {
            return {};
        },
        created() {
            if (!this.$store.state.user || this.$store.state.user.id != this.id) {
                this.toastPopUp("error", "Necessita de estar autenticado na conta");
            }
        },
        methods: {
            alterarEmail: function () {
                axios.put("/api/register/novoEmail/" + this.id, {'email': this.emailCompleto}).then(response => {
                    this.toastPopUp("success", "Email alterado com sucesso");
                    this.logout();
                }).catch(errors => {
                    console.log(errors);
                });
            },
            logout() {
                axios.get('/api/logout').then(response => {
                    this.$store.commit('clearUserAndToken');
                    this.$socket.emit('user_exit', this.$store.state.user);
                    this.$router.push({name: 'index'});
                }).catch(error => {
                    this.$store.commit('clearUserAndToken');
                    console.log(error);
                });
            },
        },
        computed:{
            emailCompleto: function() {
                return this.email + '@' + window.location.search.substr(1);
            }
        }
    }
</script>
