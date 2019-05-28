<template>
    <div @focusout="closeLists">
        <!-- Modal Add Order-->
        <div class="modal fade" id="addAtividadeModal" tabindex="-1" role="dialog" aria-labelledby="addAtividadeModal"
             data-keyboard="false" data-backdrop="static" aria-hidden="true">
            <div class="modal-dialog modal-md" role="document">
                <div class="modal-content">
                    <div class="container box" @click="closeLists">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addAtividadeModal">{{getTitle}}</h5>
                            <button type="button" @click="cancel()" class="close" data-dismiss="modal"
                                    aria-label="Close" :disabled="isLoading">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <br>
                        <div class="form-group">
                            <v-text-field id="inputTitulo"
                                          v-model="atividade.titulo"
                                          label="Título"
                                          :rules="[v => !!v || 'Titulo é obrigatório']"
                                          required
                            ></v-text-field>
                        </div>
                        <div class="form-group">
                            <v-textarea
                                id="inputDescricao"
                                v-model="atividade.descricao"
                                label="Descrição"
                                :rules="[v => !!v || 'Descrição é obrigatória',
                                             v => v && v.length >= 25 || 'minimo 25 caracteres']"
                                counter="1000"
                                required
                            ></v-textarea>
                        </div>
                        <br>
                        <div @click="setOpenList('tipo')">
                            <v-select
                                label="Tipo"
                                v-model="tipoSelected"
                                :items="tipos"
                                :rules="[v => !!v || 'Tipo de atividade é obrigatória']"
                                class="input-group--focused"
                                required
                                clearable
                                ref="selectTipo"
                            ></v-select>
                        </div>
                        <div class="form-group" v-if="tipoSelected === 'outro'">
                            <v-text-field id="inputTipoOutro"
                                          v-model="outroTipo"
                                          label="Indique o tipo da atividade"
                                          :rules="[v => !!v || 'Tipo é obrigatório']"
                                          required
                            ></v-text-field>
                        </div>
                        <br>
                        <div class="form-group">
                            <v-checkbox
                                v-model="temGrupo"
                                label="Atividade em grupo?"
                            ></v-checkbox>
                            <v-text-field
                                v-if="temGrupo"
                                type="number"
                                min="2"
                                step="1"
                                default="2"
                                id="inputNumeroElementos"
                                v-model="numeroElementos"
                                label="Numero de elementos por grupo"
                                :rules="[v => !!v && Number.isInteger(Number(v)) && v > 1 || 'Numero de elementos é obrigatório ser inteiro e maior que 1']"
                                required
                            ></v-text-field>
                        </div>
                        <v-spacer></v-spacer>
                        <div class="form-group">
                            <v-checkbox
                                v-model="chatExist"
                                label="Atividade tem Chat?"
                            ></v-checkbox>
                            <v-textarea
                                v-if="chatExist"
                                id="inputChatAssunto"
                                v-model="chatAssunto"
                                label="Chat Descrição"
                                counter="150"
                                required
                            ></v-textarea>
                        </div>
                        <v-spacer></v-spacer>
                        <div @click="setOpenList('visibilidade')">
                            <v-select
                                label="Visibilidade"
                                v-model="atividade.visibilidade"
                                :items="visibilidades"
                                :rules="[v => !!v || 'Visibilidade é obrigatória']"
                                class="input-group--focused"
                                required
                                clearable
                                ref="selectVisibilidade"
                            ></v-select>
                        </div>
                        <div @click="setOpenList('alunos')">
                            <v-combobox
                                v-model="atividade.participantes"
                                :items="alunos"
                                item-text="nome"
                                label="Alunos"
                                multiple
                                chips
                                class="input-group--focused"
                                deletable-chips
                                ref="selectAlunos"
                                autofocus
                                hide-no-data
                            ></v-combobox>
                        </div>
                        <div @click="setOpenList('patrimonios')">
                            <v-combobox
                                v-model="patrimoniosSelecionados"
                                :items="patrimonios"
                                item-text="nome"
                                label="Patrimonios"
                                multiple
                                chips
                                class="input-group--focused"
                                deletable-chips
                                ref="selectPatrimonios"
                                :rules="[v => v.length != 0 || 'Património é obrigatório']"
                                autofocus
                                hide-no-data
                                required
                            ></v-combobox>
                        </div>
                    </div>
                    <div v-if="!isLoading" class="modal-footer">
                        <button v-if="!atividade.id" class="btn btn-info" v-on:click.prevent="save"
                                :disabled="hasErrors">
                            Registar
                        </button>
                        <button v-else class="btn btn-info" v-on:click.prevent="edit" :disabled="hasErrors">
                            Guardar
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
        props: ['atividade'],
        created() {
            this.getTipos();
            if (this.$store.state.user.tipo === 'professor') {
                this.getAlunos();
                this.getPatrimonios();
            }
        },
        data: function () {
            return {
                tipos: [],
                tipoSelected: '',
                outroTipo: '',
                visibilidades: ['privado', 'publico', 'visivel para a escola'],
                alunos: [],
                patrimonios: [],
                selTipoAberto: false,
                selVisibilidadeAberto: false,
                selAlunosAberto: false,
                selPatrimoniosAbertos: false,
                chatExist: false,
                patrimoniosSelecionados: [],
                chatAssunto: '',
                temGrupo: false,
                numeroElementos: 2,
                isLoading: false,
            };
        },
        methods: {
            isCreated() {
                return (this.atividade.id && this.atividade.id) > 0 ? false : true;
            },
            getTipos() {
                this.isLoading = true;
                axios.get('/api/atividades/tipos')
                    .then(response => {
                        this.tipos = response.data.data;
                        this.tipos.push('outro');
                        this.isLoading = false;
                    }).catch(error => {
                        this.toastPopUp("error", `${error.response.data.message}`);
                        this.isLoading = false;
                    });
            },
            save: function () {
                this.isLoading = true;
                this.prepararAtividade();
                axios.post('/api/atividades', this.atividade).then(response => {
                    this.toastPopUp("success", "Atividade Criada!");
                    this.$socket.emit('multiplos_atualizar_notificacoes', this.atividade.participantes);
                    this.$emit('atualizar');
                    if (this.tipoSelected === 'outro'){
                        this.$emit('atualizarTipos');
                    }
                    $('#addAtividadeModal').modal('hide');
                    this.isLoading = false;
                    this.cleanForm();
                }).catch(error => {
                    this.isLoading = false;
                    this.toastPopUp("error", `${error.response.data.errors[Object.keys(error.response.data.errors)[0]]}`);
                })
            },
            edit: function () {
                this.isLoading = true;
                this.prepararAtividade();
                axios.put('/api/atividades/' + this.atividade.id, this.atividade).then(response => {
                    this.toastPopUp("success", "Atividade Atualizada!");
                    this.$socket.emit('multiplos_atualizar_notificacoes', this.atividade.participantes);
                    this.$emit('atualizar');
                    if (this.tipoSelected === 'outro'){
                        this.$emit('atualizarTipos');
                    }
                    $('#addAtividadeModal').modal('hide');
                    this.isLoading = false;
                    this.cleanForm();
                }).catch(error => {
                    this.isLoading = false;
                    this.toastPopUp("error", `${error.response.data.errors[Object.keys(error.response.data.errors)[0]]}`);
                })
            },
            prepararAtividade() {
                this.atividade.chatExist = this.chatExist;
                this.atividade.chat = {assunto: this.chatAssunto};
                this.atividade.patrimonios = this.patrimoniosSelecionados;
                this.atividade.tipo = this.tipoSelected === 'outro' ? this.outroTipo : this.tipoSelected;
                this.atividade.numeroElementos = this.temGrupo ? this.numeroElementos : 1;
            },
            cancel: function () {
                $('#addAtividadeModal').modal('hide');
                this.cleanForm();
            },
            cleanForm: function () {
                this.atividade.id = null;
                this.atividade.titulo = "";
                this.atividade.descricao = "";
                this.atividade.numeroElementos = "";
                this.atividade.visibilidade = "";
                this.atividade.data = "";
                this.atividade.participantes = [];
                this.atividade.patrimonios = [];
                this.patrimoniosSelecionados = [];
                this.tipoSelected = '';
                this.outroTipo = '';
                this.chatAssunto = "";
                this.chatExist = false;
                this.closeLists();
            },
            getAlunos(url = '/api/me/escola/alunos') {
                axios.get(url)
                    .then(response => {
                        this.alunos = response.data.data;
                    }).catch(error => {
                        this.toastPopUp("error", `${error.response.data.message}`);
                    });
            },
            getPatrimonios(url = '/api/patrimonios') {
                axios.get(url)
                    .then(response => {
                        this.patrimonios = response.data.data;
                    }).catch(error => {
                        this.toastPopUp("error", `${error.response.data.message}`);
                    });
            },
            setOpenList(lista) {
                setTimeout(() => {
                    if (lista == "tipo" && this.$refs.selectTipo.isMenuActive == true) {
                        setTimeout(() => {
                            this.selTipoAberto = true;
                        }, 30);
                    }
                    if (lista == "visibilidade" && this.$refs.selectVisibilidade.isMenuActive == true) {
                        setTimeout(() => {
                            this.selVisibilidadeAberto = true;
                        }, 30);
                    }
                    if (lista == "alunos" && this.$refs.selectAlunos.isMenuActive == true) {
                        setTimeout(() => {
                            this.selAlunosAberto = true;
                        }, 30);
                    }
                    if (lista == "patrimonios" && this.$refs.selectPatrimonios.isMenuActive == true) {
                        setTimeout(() => {
                            this.selPatrimoniosAberto = true;
                        }, 30);
                    }
                }, 10)
            },
            closeLists() {
                if (this.selTipoAberto == true) {
                    this.selTipoAberto = false;
                    this.$refs.selectTipo.isMenuActive = false;
                }
                if (this.selVisibilidadeAberto == true) {
                    this.selVisibilidadeAberto = false;
                    this.$refs.selectVisibilidade.isMenuActive = false;
                }
                if (this.selAlunosAberto == true) {
                    this.selAlunosAberto = false;
                    this.$refs.selectAlunos.isMenuActive = false;
                }
                if (this.selPatrimoniosAberto == true) {
                    this.selPatrimoniosAberto = false;
                    this.$refs.selectPatrimonios.isMenuActive = false;
                }
            },
        },
        computed: {
            hasErrors: function () {
                return (this.temGrupo && (!Number.isInteger(Number(this.numeroElementos)) || this.numeroElementos < 2) ||
                    this.patrimoniosSelecionados.length === 0 || !this.atividade.titulo || !this.atividade.descricao ||
                    this.atividade.descricao.length < 25 || !this.tipoSelected ||
                    this.tipoSelected === 'outro' && !this.outroTipo || !this.atividade.visibilidade);
            },
            getTitle: function () {
                return this.isCreated() ? "Criar Atividade" : "Editar Atividade";
            },
        },
        watch: {
            atividade: function (atividade, oldAtividade) {
                if (atividade.id) {
                    if (this.atividade.patrimonios) {
                        this.patrimoniosSelecionados = this.atividade.patrimonios;
                    } else {
                        this.patrimoniosSelecionados = [];
                    }
                    if (this.atividade.chat && this.atividade.chat.id) {
                        this.chatAssunto = this.atividade.chat.assunto;
                        this.chatExist = true;
                    } else {
                        if (!this.atividade.chat || this.atividade.chat && !this.atividade.chat.assunto) {
                            this.chatAssunto = "";
                        }
                        this.chatExist = false;
                    }
                    if (atividade.numeroElementos == 1){
                        this.temGrupo = false;
                    } else{
                        this.temGrupo = true;
                        this.numeroElementos = atividade.numeroElementos;
                    }
                    this.tipoSelected = this.atividade.tipo;
                }else{
                    this.cleanForm();
                }
            }
        }
    }
</script>
