<template>
    <div>
        <!-- Modal Add Order-->
        <div class="modal fade" id="addPatrimonioModal" tabindex="-1" role="dialog" aria-labelledby="addPatrimonioModal"
             aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="container box">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addPatrimonioModal">{{getTitle()}}</h5>
                            <button type="button" @click="cancel()" class="close" data-dismiss="modal"
                                    aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="form-group">
                            <v-text-field id="inputNome"
                                          v-model="patrimonio.nome"
                                          label="Nome"
                                          :rules="[v => !!v || 'Nome é obrigatório']"
                                          required
                            ></v-text-field>
                        </div>
                        <!--<div id="app">
                            <ckeditor :editor="editor" :config="editorConfig" :value="editorData" v-model="editorData"></ckeditor>
                        </div>-->

                        <div class="form-group">
                            <v-textarea
                                name="descricao"
                                v-model="patrimonio.descricao"
                                box
                                label="Descrição"
                                auto-grow
                                counter="3000"
                                :rules="[rules.length(3000)]"
                                required
                            ></v-textarea>
                        </div>
                        <v-container>
                            <v-layout class="form-group" row align-center>

                                <v-select
                                    fixed
                                    label="Distrito"
                                    v-model="patrimonio.distrito"
                                    :items="distritos"
                                    :rules="[v => !!v || 'Distrito é obrigatório']"
                                    class="input-group--focused"
                                    required
                                ></v-select>
                                <v-spacer></v-spacer>
                                <v-select
                                    fixed
                                    label="Época"
                                    v-model="patrimonio.epoca"
                                    :items="epocas"
                                    :rules="[v => !!v || 'Época histórica é obrigatória']"
                                    class="input-group--focused"
                                    required
                                ></v-select>

                                <v-spacer></v-spacer>
                                <v-select
                                    fixed
                                    label="Ciclo"
                                    v-model="patrimonio.ciclo"
                                    :items="ciclos"
                                    :rules="[v => !!v || 'Ciclo é obrigatória']"
                                    class="input-group--focused"
                                    required
                                ></v-select>

                            </v-layout>
                        </v-container>

                        <div class="custom-file">
                            <label class="custom-file-label" for="upload-files">{{getFilesText()}}</label>
                            <input id="upload-files" type="file" multiple class="form-control custom-file-input"
                                   @change="handleFile">
                        </div>
                        <br>
                        <div class="custom-file">

                            <input id="upload-file2" type="file" accept=".png, .jpg, .jpeg" multiple
                                   class="form-control custom-file-input"
                                   @change="handleFile">
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button v-if="isCreated()" class="btn btn-info" v-on:click.prevent="save">Registar</button>
                        <button v-else class="btn btn-info" v-on:click.prevent="update">Guardar</button>
                        <button class="btn btn-danger" v-on:click.prevent="cancel">Cancelar</button>
                    </div>

                </div>

            </div>
        </div>
    </div>
</template>

<script>
    //import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

    module.exports = {

        props: ['patrimonio'],


        data: function () {
            return {
                /* valid: false,
                 editor: ClassicEditor,
                 editorData: '',
                 editorConfig: {
                     toolbar: ['heading', '|', 'Bold', 'Italic', 'bulletedList', 'numberedList', 'blockQuote', 'Link', 'Undo', 'Redo'],

                     heading: {
                         options: [
                             {model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph'},
                             {model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1'},
                             {model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2'},
                             {model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3'}
                         ]
                     },
                 },**/
                distritos: ['Aveiro', 'Beja', 'Braga', 'Bragança', 'Castelo Branco', 'Coimbra', 'Évora', 'Faro',
                    'Guarda', 'Leiria', 'Lisboa', 'Portalegre', 'Porto', 'Santarém', 'Setúbal', 'Viana do Castelo',
                    'Vila Real', 'Viseu', 'Açores', 'Madeira'],
                epocas: ['pré-história', 'idade antiga', 'idade média', 'idade contemporânea'],
                ciclos: ['1º ciclo', '2º ciclo', '3º ciclo', 'secundário'],

                rules: {
                    length: len => v => (v || '').length <= len || `Numero de caracteres invalido, Maximo ${len}`,
                    required: v => !!v || 'Este campo é necessário'
                },
                attachments: [],
            };
        },
        methods: {
            isCreated() {
                return this.patrimonio.id > 0 ? false : true;
            },
            getTitle() {
                return this.isCreated() ? "Criar Patrimonio" : "Editar Patrimonio";
            },

            getFilesText() {
                if (this.attachments.length == 0) {
                    return "Carregar Imagens"
                }
                if (this.attachments.length == 1) {
                    return "1 Imagem Carregada"
                }

                return this.attachments.length + " imagens Carregadas";

            },
            handleFile: function (e) {
                let files = e.target.files || e.dataTransfer.files;

                if (!files.length) {
                    return false;
                }

                for (let i = 0; i < files.length; i++) {
                    if (!files[i].type.includes("image/")) {
                        this.$toasted.error('File must be an image', {
                            position: "bottom-center",
                            duration: 3000,
                        });
                        return;
                    }
                    this.attachments.push(files[i]);
                }

                console.log(this.attachments);

            },
            formCreate: function () {
                let formData = new FormData();
                formData.append('nome', this.patrimonio.nome);
                formData.append('descricao', this.patrimonio.descricao);
                formData.append('distrito', this.patrimonio.distrito);
                formData.append('ciclo', this.patrimonio.ciclo);
                formData.append('epoca', this.patrimonio.epoca);
                if (this.isCreated()) {
                    for (let i = 0; i < this.attachments.length; i++) {
                        formData.append('imagens[]', this.attachments[i]);
                    }
                }else{
                    for (let i = 0; i < this.attachments.length; i++) {
                        formData.append('novas_imagens[]', this.attachments[i]);
                    }
                }

                return formData;
            },
            hasErrors: function () {
                if (!this.patrimonio.nome || !this.patrimonio.ciclo || !this.patrimonio.epoca || !this.patrimonio.distrito || !this.patrimonio.descricao) {
                    return true;
                }
                return false;
            },
            save: function () {
                if (!this.hasErrors()) {
                    const config = {
                        headers: {'content-type': 'multipart/form-data'}
                    };
                    axios.post('/api/patrimonios', this.patrimonio, config).then(response => {
                        this.toastPopUp("success", "Património Criado!");
                        this.cleanForm();
                        this.$emit('getPat');
                        $('#addPatrimonioModal').modal('hide');
                    }).catch(error => {
                        this.toastPopUp("error", `${error.response.data.message}`);
                    })
                }
            },
            update() {
                if (!this.hasErrors()) {
                    const config = {
                        headers: {'content-type': 'multipart/form-data'}
                    };
                    axios.post('/api/patrimonios/' + this.patrimonio.id, this.formCreate(), config).then(response => {
                        this.toastPopUp("success", "Património Editado!");
                        this.cleanForm();
                        this.$emit('getPat');
                        $('#addPatrimonioModal').modal('hide');

                    }).catch(function (error) {
                        this.toastPopUp("error", `${error.response.data.message}`);
                        console.log(error);
                    });
                }
            },


            cancel: function () {
                this.cleanForm();
                $('#addPatrimonioModal').modal('hide');
            },
            cleanForm: function () {
                this.patrimonio.id = undefined;
                this.patrimonio.nome = "";
                this.patrimonio.descricao = "";
                this.patrimonio.distrito = "";
                this.patrimonio.epoca = "";
                this.patrimonio.ciclo = "";
                this.attachments = [];
            }
        },
    };
</script>
