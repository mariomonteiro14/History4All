<template>
    <div>
        <div class="modal fade" id="confirmacaoEmailModal" tabindex="-1" role="dialog" aria-labelledby="confirmacaoEmailModal"
             aria-hidden="true" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="container box">
                        <div class="modal-header">
                            <h5 class="modal-title" id="confirmacaoEmailModal">Necessita de confirmar o seu email</h5>
                            <button type="button" @click="cancel()" class="close" data-dismiss="modal"
                                    aria-label="Close" :disabled="isLoading">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <v-container>
                            <v-layout class="form-group" row align-center>
                                <v-text-field id="inputEmail"
                                            v-model="email"
                                            label="Email"
                                            :rules="emailRules"
                                            clearable
                                            required
                                ></v-text-field>
                                <div class="grey--text">
                                    <v-btn color="success" @click="gerarCodigo" :disabled="isLoading">Enviar código por email
                                        <template v-slot:append v-if="!codigo">
                                            <v-tooltip left>
                                                <template v-slot:activator="{ on }">
                                                    <v-icon v-on="on">help</v-icon>
                                                </template>
                                                Clique aqui para reenviar o email com o código
                                            </v-tooltip>
                                        </template>
                                    </v-btn>
                                </div>
                                <v-text-field class="text-sm-left" v-if="emailEnviado"
                                    v-model="codigo"
                                    label="Código de acesso"
                                    :rules="[v => !!v || 'Código de acesso']"
                                    clearable
                                    :disabled="isLoading"
                                >
                                    <template v-slot:append v-if="!codigo">
                                        <v-tooltip left>
                                            <template v-slot:activator="{ on }">
                                                <v-icon v-on="on">help</v-icon>
                                            </template>
                                            Insira o seu código de acesso que lhe foi enviado por email
                                        </v-tooltip>
                                    </template>
                                </v-text-field>
                            </v-layout>
                        </v-container>
                        <div v-if="!isLoading" class="modal-footer">
                            <button class="btn btn-info" v-on:click.prevent="confirmarCodigo" :disabled="!codigo">confirmar código</button>
                            <button class="btn btn-danger" v-on:click.prevent="cancel">Cancelar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['forum'],

        data: function () {
            return {
                emailRules: [
                    (v) => !!v || 'email é obrigatório',
                    (v) => /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(v) || 'email tem de ser valido'
                ],
                email: '',
                codigo: '',
                emailEnviado: false,
                isLoading: false
            };
        },
        methods: {
            gerarCodigo(){
                this.isLoading= true;
                axios.post('/api/forums/' + this.forum.id + '/compararEmails', {'user_email': this.email}).then(response => {
                    this.toastPopUp("success", "Enviámos-lhe um email com o código de acesso");
                    this.emailEnviado = true;
                    this.isLoading= false;
                }).catch(error => {
                    this.isLoading= false;
                    this.toastErrorApi(error);
                })
            },
            confirmarCodigo() {
                axios.post('/api/forums/compararCodigo', {'user_email': this.email, 'codigo': this.codigo}).then(response => {
                    $('#confirmacaoEmailModal').modal('hide');
                    this.$emit('credenciais', this.email, this.codigo);
                    this.isLoading= false;
                    this.clearForm();
                    this.$emit('emailConfirmado');
                }).catch(error => {
                    this.isLoading= false;
                    this.toastErrorApi(error);
                })
            },
            clearForm(){
                this.email = '';
                this.codigo = '';
                this.emailEnviado = false;
            },
            cancel: function () {
                this.clearForm();
                $('#confirmacaoEmailModal').modal('hide');
            }
        }
    };

</script>