<template>
    <div @focusout="closeLists">
        <!-- Modal Add Order-->
        <div class="modal fade" id="enviarNotificacaoModal" tabindex="-1" role="dialog" aria-labelledby="enviarNotificacaoModal"
             aria-hidden="true" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog modal-md" role="document">
                <div class="modal-content">
                    <div class="container box" @click="closeLists">
                        <div class="modal-header">
                            <h5 class="modal-title" id="enviarNotificacaoModal">{{getTitle}}</h5>
                            <button type="button" @click="cancel()" class="close" data-dismiss="modal"
                                    aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <br>
                        <div class="form-group">
                            <v-textarea id="inputMensagem"
                                    v-model="mensagem"
                                    label="mensagem"
                                    :rules="[v => !!v || 'Mensagem é obrigatória']"
                                    required
                            ></v-textarea>
                        </div>
                        <div @click="setOpenList('users')">
                            <v-combobox
                                    v-model="usersSelected"
                                    :items="users"
                                    item-text="nome"
                                    label="Users"
                                    multiple
                                    chips
                                    class="input-group--focused"
                                    deletable-chips
                                    ref="selectUsers"
                                    autofocus
                                    @click="selUsersAberto=true"
                                    :disabled="!usersSelected || usersSelected.length == 0"
                            ></v-combobox>
                        </div>
                    </div>
                    <div v-if="aEnviar" class="modal-footer">
                        <button class="btn btn-info" v-on:click.prevent="enviar" :disabled="hasErrors">
                            Enviar
                        </button>
                        <button class="btn btn-danger" v-on:click.prevent="cancel">Cancelar</button>
                    </div>
                    <div v-else class="modal-footer">
                        <loader color="green" size="32px"></loader>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>

<script>
    export default {
        props: ['tipo', 'users'],
        data: function () {
            return {
                mensagem: '',
                selUsersAberto: false,
                usersSelected: [],
                aEnviar: false,
            };
        },
        methods: {
            enviar: function () {
                this.aEnviar = true;
                axios.post('/api/notificacoes', {
                    'de': (this.tipo !== 'turma' ? 'coordenador da atividade: ' + this.tipo :
                        this.$store.state.user.tipo + ' ' + this.$store.state.user.nome),
                    'users': this.usersSelected,
                    'mensagem': this.mensagem
                }).then(response => {
                    this.$socket.emit('nova_notificacao', response.data, this.usersSelected);
                    this.toastPopUp("success", "Notificação enviada com sucesso!");
                    this.cancel();
                    this.aEnviar = false;
                }).catch(error => {
                    this.aEnviar = false;
                    this.toastPopUp("error", `${error.response.data.message}`);
                });
            },
            cancel: function () {
                this.mensagem = '';
                this.selUsersAberto = false;
                $('#enviarNotificacaoModal').modal('hide');
            },
            setOpenList(lista) {
                setTimeout(() => {
                    if (lista == "users" && this.$refs.selectUsers.isMenuActive == true) {
                        setTimeout(() => {
                            this.selUsersAberto = true;
                        }, 30);
                    }
                }, 10)
            },
            closeLists() {
                if (this.selUsersAberto == true) {
                    this.selUsersAberto = false;
                    this.$refs.selectUsers.isMenuActive = false;
                }
            }
        },
        computed: {
            hasErrors: function () {
                return this.mensagem.length === 0 || this.usersSelected.length === 0;
            },
            getTitle() {
                return this.tipo === 'turma' ? 'Notificação para todos os alunos da turma' :
                    "Notificação para todos os alunos que estão com a atividade pendente";
            },
        },
        watch:{
            users(){
                this.usersSelected = this.users;
            }
        }
    }
</script>
