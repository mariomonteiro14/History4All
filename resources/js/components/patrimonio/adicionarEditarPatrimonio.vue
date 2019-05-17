<template>
    <div>
        <!-- Modal Add Order-->
        <div class="modal fade" id="addPatrimonioModal" tabindex="-1" role="dialog" aria-labelledby="addPatrimonioModal"
             aria-hidden="true" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog modal-xl" role="document">
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

                        <div id="app">
                            <ckeditor :editor="editor" :config="editorConfig" :value="patrimonio.descricao"
                                      v-model="patrimonio.descricao" required></ckeditor>
                        </div>

                        <div class="form-group">
                            <v-textarea
                                    id="inputNome"
                                    v-model="links"
                                    label="Links separados por espaço"
                            ></v-textarea>
                        </div>

                        <v-container>
                            <v-layout class="form-group" row align-center>

                                <v-select
                                    label="Distrito"
                                    v-model="patrimonio.distrito"
                                    :items="distritos"
                                    :rules="[v => !!v || 'Distrito é obrigatório']"
                                    class="input-group--focused"
                                    required
                                ></v-select>
                                <v-spacer></v-spacer>
                                <v-select
                                    label="Época"
                                    v-model="patrimonio.epoca"
                                    :items="epocas"
                                    :rules="[v => !!v || 'Época histórica é obrigatória']"
                                    class="input-group--focused"
                                    required
                                ></v-select>

                                <v-spacer></v-spacer>
                                <v-select
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
                               @change="handleFile" accept=".png, .jpg, .jpeg">
                    </div>
                        <br>
                        <!-- <vue-select-image :dataImages="dataImages"
                                           :is-multiple="true"
                                           :selectedImages="initialSelected"
                                           @onselectmultipleimage="onSelectMultipleImage">
                         </vue-select-image>-->
                        <v-container v-if="patrimonio.imagens && patrimonio.imagens.length > 0">
                            <h4>{{getTextRemoveFiles()}}</h4>
                            <v-layout class="form-group" fluid wrap align-center>
                                <span class="border" v-for="image in patrimonio.imagens" v-bind:id="image" @click="selectImage(image)">
                                    <img class="preview" v-bind:src="getPatrimonioPhoto(image)">
                                </span>
                            </v-layout>
                        </v-container>

                        <!--<v-list-tile v-for="(image, index) in patrimonio.imagens" :key="image">
                            <v-list-tile-content>
                                <v-checkbox :value="image"
                                            :key="image"
                                            :src="getPatrimonioPhoto(image)"
                                            v-model="removeImagesSelected">
                                    <img class="preview" v-bind:src="getPatrimonioPhoto(image)">
                                </v-checkbox>
                            </v-list-tile-content>
                        </v-list-tile>-->
                    </div>

                    <div v-if="!isLoading" class="modal-footer">
                        <button v-if="isCreated()" class="btn btn-info" v-on:click.prevent="save" :disabled="hasErrors">Registar</button>
                        <button v-else class="btn btn-info" v-on:click.prevent="update" :disabled="hasErrors">Guardar</button>
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

        props: ['patrimonio'],

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
                removeImagesSelected: [],
                links: '',
                isLoading: false,
            };
        },
        methods: {
            isCreated() {
                return this.patrimonio.id > 0 ? false : true;
            },
            getTitle() {
                return this.isCreated() ? "Adicionar Patrimonio" : "Editar Patrimonio";
            },

            getFilesText() {
                if (!this.isCreated() && this.attachments.length == 0) {
                    return "Adicionar Imagens"
                }
                if (this.attachments.length == 0) {
                    return "Carregar Imagens"
                }
                if (this.attachments.length == 1) {
                    return "1 Imagem Carregada"
                }

                return this.attachments.length + " imagens Carregadas";

            },
            getTextRemoveFiles() {
                if (this.removeImagesSelected.length == 0) {
                    return "Selecione imagens para remover"
                }
                if (this.removeImagesSelected.length == 0) {
                    return "1 imagem a remover";
                }
                return this.removeImagesSelected.length + " imagens a remover";
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
                } else {
                    for (let i = 0; i < this.attachments.length; i++) {
                        formData.append('novas_imagens[]', this.attachments[i]);
                    }
                    for (let i = 0; i < this.removeImagesSelected.length; i++) {
                        formData.append('eliminar_imagens[]', this.removeImagesSelected[i]);
                    }
                }

                return formData;
            },
            save: function () {
                this.isLoading= true;

                const config = {
                    headers: {'content-type': 'multipart/form-data'}
                };
                axios.post('/api/patrimonios', this.formatarDescricaoDoPatrimonio(), config).then(response => {
                    this.toastPopUp("success", "Património Criado!");
                    this.cleanForm();
                    this.$emit('getPat');
                    $('#addPatrimonioModal').modal('hide');
                    this.isLoading= false;
                }).catch(error => {
                    this.isLoading= false;
                    this.toastPopUp("error", `${error.response.data.message}`);
                })
            },
            update() {
                this.isLoading= true;

                const config = {
                    headers: {'content-type': 'multipart/form-data'}
                };
                axios.post('/api/patrimonios/' + this.patrimonio.id, this.formatarDescricaoDoPatrimonio(), config).then(response => {
                    this.toastPopUp("success", "Património Editado!");
                    this.cleanForm();
                    this.$emit('getPat');
                    $('#addPatrimonioModal').modal('hide');
                    this.isLoading= false;

                }).catch(function (error) {
                    this.isLoading= false;

                    this.toastPopUp("error", `${error.response.data.message}`);
                    console.log(error);
                });
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
                this.patrimonio.imagens = undefined;
                this.attachments = [];
                this.removeImagesSelected = [];
                this.links = '';
            },
            selectImage(imagem) {
                var index = this.removeImagesSelected.indexOf(imagem);
                if (index !== -1) {
                    this.removeImagesSelected.splice(index, 1);
                    document.getElementById(imagem).classList.remove("border-success");
                } else {
                    this.removeImagesSelected.push(imagem);
                    document.getElementById(imagem).classList.add("border-success");
                }
            },
            formatarDescricaoDoPatrimonio(){
                let $patrimonio = this.formCreate();
                if (this.links) {
                    let descricao = $patrimonio.get('descricao') + "<div id='links'><h4>Links sobre o património:</h4><ul>";
                    this.links.split(" ").forEach(link => {descricao += "<li><a href='" + link + "'\>" + link + "</a></li>"});
                    descricao += "</ul></div>";
                    $patrimonio.set('descricao', descricao);
                }
                return $patrimonio;
            }
        },
        watch: {
            patrimonio: function (newPatrim, oldPatrim) {
                this.removeImagesSelected = [];
                if (oldPatrim.id) {
                    oldPatrim.imagens.forEach(function (img) {
                        if (document.getElementById(img)) {
                            document.getElementById(img).classList.remove("border-success");
                        }

                    });
                }
                if (newPatrim.descricao.includes("<div id='links'><h4>Links sobre o património:</h4><ul><li>")){
                    let linksComTags = newPatrim.descricao.split("<div id='links'><h4>Links sobre o património:</h4><ul><li>")[1].split("<li>");
                    let linksFormatadosComEspaco = '';
                    linksComTags.forEach(link => {
                        linksFormatadosComEspaco += link.slice(link.indexOf('>') + 1, link.indexOf('</')) + ' ';
                    });
                    this.links = linksFormatadosComEspaco.slice(0, linksFormatadosComEspaco.length - 1);
                    let descricaoComTag = newPatrim.descricao.split("<div id='links'>")[0];
                    this.patrimonio.descricao = descricaoComTag.slice(0, descricaoComTag.length - 4);
                }
            }
        },
        computed: {
            hasErrors: function () {
                if (!this.patrimonio.nome || !this.patrimonio.ciclo || !this.patrimonio.epoca || !this.patrimonio.distrito || !this.patrimonio.descricao) {
                    return true;
                }
                return false;
            },
        }
    };

</script>
<style>
    img.preview {
        width: 200px;
        background-color: white;
        border: 1px solid #DDD;
        padding: 5px;
    }
    .ck-editor__editable {
        min-height: 300px;
    }
</style>
