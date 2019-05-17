<template>
    <div>
        <!-- Modal Add Order-->
        <div class="modal fade" id="resetPasswordModal" tabindex="-1" role="dialog" aria-labelledby="resetPasswordModal"
             aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="container box">
                        <div class="modal-header">
                            <h5 class="modal-title">Enviar email para fazer reset à sua password</h5>
                            <button type="button" @click="cancel()" class="close" data-dismiss="modal"
                                    aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="form-group">
                            <v-text-field id="inputEmail"
                                          v-model="email"
                                          label="Seu email"
                                          :rules="emailRules"
                                          clearable
                                          required
                            ></v-text-field>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-info" :disabled="hasErrors || aEnviarEmail" v-on:click.prevent="save">Enviar email</button>
                        <button class="btn btn-danger" v-on:click.prevent="cancel">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data: function () {
            return {
                email: '',
                emailRules: [
                    (v) => !!v || 'email é obrigatório',
                    (v) => /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(v) || 'email tem de ser valido'
                ],
                aEnviarEmail: false
            };
        },
        methods: {
            save: function () {
                this.aEnviarEmail = true;
                axios.post('/api/sendEmail/resetPassword', {'email' : this.email}).then(response => {
                    this.toastPopUp("success", "Email Enviado");
                    this.email = '';
                    this.aEnviarEmail = false;
                    $('#resetPasswordModal').modal('hide');
                    this.$emit('fechar');
                }).catch(error => {
                    this.aEnviarEmail = false;
                    this.toastPopUp("error", `${error.response.data.message}`);
                })
            },
            cancel: function () {
                this.email = '';
                $('#resetPasswordModal').modal('hide');
            }
        },
        computed: {
            hasErrors: function () {
                if (!this.email){
                    return true;
                }
                var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                if (!re.test(String(this.email).toLowerCase())) {
                    return true;
                }
                return false;
            }
        }
    }

</script>
