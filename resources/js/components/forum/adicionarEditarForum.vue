<template>
    <div>
        <div class="modal fade" id="addForumModal" tabindex="-1" role="dialog" aria-labelledby="addForumModal"
             aria-hidden="true" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="container box">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addForumModal">{{getTitle()}}</h5>
                            <button type="button" v-on:click.prevent="cancel" class="close" data-dismiss="modal"
                                    aria-label="Close" :disabled="isLoading">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <v-container>
                            <div class="form-group">
                                <v-text-field id="inputTitulo"
                                              v-model="forum.titulo"
                                              label="Titulo"
                                              :rules="[v => !!v || 'Titulo é obrigatório',
                                                  v => v && v.length <= 255 || 'máximo 255 caracteres']"
                                              counter="255"
                                              clearable
                                              required
                                              :disabled="emailEnviado"
                                >
                                    <template v-slot:append v-if="!forum.titulo">
                                        <v-tooltip left>
                                            <template v-slot:activator="{ on }">
                                                <v-icon v-on="on">help</v-icon>
                                            </template>
                                            <span>Assunto ou topico de discussão</span>
                                        </v-tooltip>
                                    </template>
                                </v-text-field>
                            </div>
                            <div class="form-group">
                                <v-combobox
                                    v-model="forum.patrimonios"
                                    :items="patrimonios"
                                    item-text="nome"
                                    label="Patrimonios associados"
                                    multiple
                                    chips
                                    outlined
                                    deletable-chips
                                    :rules="[v => v.length != 0 || 'Patrimónios são obrigatórios']"
                                    hide-no-data
                                    required
                                    @input="forum.patrimonios = validarInputComboBox(forum.patrimonios)"
                                    :disabled="emailEnviado"
                                >
                                    <template v-slot:append v-if="forum.patrimonios && forum.patrimonios.length == 0">
                                        <v-tooltip left>
                                            <template v-slot:activator="{ on }">
                                                <v-icon v-on="on">help</v-icon>
                                            </template>
                                            Escolher patrimónios relacionados com o fórum
                                        </v-tooltip>
                                    </template>

                                    <template v-slot:selection="{ item, index }">
                                        <v-chip v-if="index <= 2">
                                            <span>{{ item.nome }}</span>
                                        </v-chip>
                                        <span
                                            v-if="index === 3"
                                            class="grey--text caption"
                                        >(+{{ forum.patrimonios.length - 3 }} outros)</span>
                                    </template>
                                </v-combobox>
                            </div>
                            <div class="form-group">
                                <v-text-field id="inputEmail" v-if="!this.$store.state.user && isCreated()"
                                              v-model="forum.user_email"
                                              label="Email"
                                              :rules="emailRules"
                                              clearable
                                              filled
                                              required
                                >
                                    <template v-slot:append v-if="!forum.user_email">
                                        <v-tooltip left>
                                            <template v-slot:activator="{ on }">
                                                <v-icon v-on="on">help</v-icon>
                                            </template>
                                            <span>Introduza o seu email de forma a confirmar a sua identidade</span>
                                        </v-tooltip>
                                    </template>
                                </v-text-field>
                            </div>

                            <div class="form-group">
                                <label for="descricaoInput" class="red--text"
                                       v-if="!forum.descricao || forum.descricao.length == 0">Descrição
                                    (obrigatório)</label>
                                <label for="descricaoInput" class="grey--text" v-else>Descrição</label>
                                <ckeditor id="descricaoInput" :editor="editor" :config="editorConfig"
                                          :value="forum.descricao"
                                          :disabled="emailEnviado"
                                          v-model="forum.descricao" required></ckeditor>
                            </div>
                        </v-container>
                    </div>

                    <div v-if="!isLoading" class="modal-footer">
                        <div class="grey" v-if="!this.$store.state.user && emailEnviado">
                            <v-btn class="btn btn-success" @click="gerarCodigo">Reenviar código
                                <template v-slot:append v-if="!forum.codigo">
                                    <v-tooltip left>
                                        <template v-slot:activator="{ on }">
                                            <v-icon v-on="on">help</v-icon>
                                        </template>
                                        Clique aqui para reenviar o email com o código
                                    </v-tooltip>
                                </template>
                            </v-btn>
                            Verifique o seu email
                            <v-text-field class="text-sm-left"
                                          v-model="forum.codigo"
                                          label="Código de acesso"
                                          :rules="[v => !!v || 'Código de acesso']"
                                          clearable
                            >
                                <template v-slot:append v-if="!forum.codigo">
                                    <v-tooltip left>
                                        <template v-slot:activator="{ on }">
                                            <v-icon v-on="on">help</v-icon>
                                        </template>
                                        Insira o seu código de acesso que lhe foi enviado por email
                                    </v-tooltip>
                                </template>
                            </v-text-field>
                        </div>
                        <div>
                            <button v-if="isCreated()" class="btn btn-info" v-on:click.prevent="save"
                                    :disabled="hasErrors || !this.$store.state.user && emailEnviado && !forum.codigo">
                                Registar
                            </button>
                            <button v-else class="btn btn-info" v-on:click.prevent="update" :disabled="hasErrors">
                                Guardar
                            </button>
                        </div>
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
    import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

    export default {

        props: ['forum', 'patrimonios'],

        data: function () {
            return {
                editor: ClassicEditor,
                editorConfig: {
                    toolbar: ['heading', '|', 'Bold', 'Italic', 'bulletedList', 'numberedList', 'blockQuote', 'Undo', 'Redo'],
                    heading: {
                        options: [
                            {model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph'},
                            {model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1'},
                            {model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2'},
                            {model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3'}
                        ]
                    },
                },
                emailRules: [
                    (v) => !!v || 'Email é obrigatório',
                    (v) => /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(v) || 'email tem de ser valido'
                ],
                rules: {
                    length: len => v => (v || '').length <= len || `Numero de caracteres invalido, Maximo ${len}`,
                    required: v => !!v || 'Este campo é necessário'
                },
                emailEnviado: false,
                isLoading: false
            };
        },
        methods: {
            isCreated() {
                return this.forum.id > 0 ? false : true;
            },
            getTitle() {
                return this.isCreated() ? "Adicionar Fórum" : "Editar Fórum";
            },
            getPatrimonios() {
                this.isLoading = true;
                axios.get('/api/patrimoniosShort')
                    .then(response => {
                        this.patrimonios = response.data.data;
                        this.isLoading = false;
                    })
                    .catch(error => {
                        this.toastErrorApi(error);
                        this.isLoading = false;
                    });
            },
            gerarCodigo() {
                axios.post('/api/forums/generateAccessCode', this.forum).then(response => {
                    this.toastPopUp("success", "Enviámos-lhe um email com o código de acesso");
                    this.emailEnviado = true;
                    this.isLoading = false;
                }).catch(error => {
                    this.isLoading = false;
                    this.toastErrorApi(error);
                })
            },
            save: function () {
                this.isLoading = true;
                if (!this.$store.state.user && !this.emailEnviado && 
                    (!this.$cookies.isKey("credentials") || this.$cookies.get("credentials")['email'] != this.forum.user_email)) {
                    this.gerarCodigo();
                    return;
                }
                if (this.$store.state.user){
                    this.forum.user_email = this.$store.state.user.email;
                } else{
                    if (this.$cookies.isKey("credentials") && !this.forum.codigo){
                        this.forum.codigo = this.$cookies.get("credentials")['codigo'];
                    }
                }
                axios.post('/api/forums', this.forum).then(response => {
                    this.toastPopUp("success", "Fórum Criado!");
                    this.emailEnviado = false;
                    this.$emit('credenciais', this.forum.user_email, this.forum.codigo);
                    this.$cookies.set("credentials", {'email': this.forum.user_email, 'codigo': this.forum.codigo}, "1d");
                    this.$emit('getFor');
                    $('#addForumModal').modal('hide');
                    this.$emit('clean');
                    this.isLoading = false;
                }).catch(error => {
                    this.isLoading = false;
                    if (!this.emailEnviado && this.$cookies.isKey("credentials") && this.$cookies.get("credentials")['email'] == this.forum.user_email) {
                        this.gerarCodigo();
                    } else {
                        this.toastErrorApi(error);
                    }
                });
            },
            update() {
                this.isLoading = true;
                axios.put('/api/forums/' + this.forum.id, this.forum).then(response => {
                    this.$emit('credenciais', this.forum.user_email, this.forum.codigo);
                    this.$cookies.set("credentials", {'email': this.forum.user_email, 'codigo': this.forum.codigo}, "1d");
                    this.toastPopUp("success", "Fórum atualizado!");
                    this.$emit('getFor');
                    this.$emit('clean');
                    $('#addForumModal').modal('hide');
                    this.isLoading = false;
                }).catch(error => {
                    this.isLoading = false;
                    this.toastErrorApi(error);
                });
            },
            cancel: function () {
                this.$emit('clean');
                this.emailEnviado = false;
                $('#addForumModal').modal('hide');
            },
            cleanForm: function () {
                this.forum.id = undefined;
                this.forum.titulo = "";
                this.forum.descricao = "";
                this.forum.user_email = "";
                this.forum.patrimonios = [];
                this.emailEnviado = false;
            },
        },
        computed: {
            hasErrors: function () {
                this.forum.patrimonios;
                if (this.$store.state.user) {
                    return !this.forum.titulo || this.forum.titulo.length > 255 || !this.forum.descricao || !this.forum.patrimonios || (this.forum.patrimonios && this.forum.patrimonios.length == 0);
                }
                let re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                return !this.forum.titulo || this.forum.titulo.length > 255 || !this.forum.descricao || !re.test(String(this.forum.user_email).toLowerCase()) || this.forum.patrimonios && this.forum.patrimonios.length == 0;
            }
        },
        watch: {
            forum(novo, anterior) {
                if (Object.entries(novo).length === 0 && novo.constructor === Object) {
                    this.cleanForm();
                }
                if (this.$store.state.user && novo.constructor === Object && !novo.user_email) {
                    novo.user_email = this.$store.state.user.email;
                }
            }
        }
    };

</script>
