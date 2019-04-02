<template>
    <v-form ref="form" v-model="valid" lazy-validation>
        <br><br><br><br><br>

        <p>Nome do património: </p>
        <v-text-field
                v-model="patrimonio.nome"
                label="Nome"
                :rules="[v => !!v || 'Nome é obrigatório']"
                required
        ></v-text-field>

        <div id="app">
            <ckeditor :editor="editor" :config="editorConfig" :value="editorData" v-model="editorData"></ckeditor>
        </div>

        <v-container fluid grid-list-xl>
            <v-layout row wrap align-center>
                <div class="form-group modal-footer" >
                    <v-select
                        label="Distrito"
                        v-model="patrimonio.distrito"
                        :items="distritos"
                        :rules="[v => !!v || 'Distrito é obrigatório']"
                        class="input-group--focused"
                        required
                    ></v-select>
                </div>
                <div class="form-group modal-footer">
                    <v-select
                        label="Época"
                        v-model="patrimonio.epoca"
                        :items="epocas"
                        :rules="[v => !!v || 'Época histórica é obrigatória']"
                        class="input-group--focused"
                        required
                    ></v-select>
                </div>
                <div class="form-group modal-footer">
                    <v-select
                        label="Ciclo"
                        v-model="patrimonio.ciclo"
                        :items="ciclos"
                        :rules="[v => !!v || 'Ciclo é obrigatória']"
                        class="input-group--focused"
                        required
                    ></v-select>
                </div>
            </v-layout>
        </v-container>
        <div>
            <v-btn small @click="validate">Registar património</v-btn>
        </div>
    </v-form>
</template>
<script type="text/javascript">
    import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

    export default {
        data: function(){
            return {
                valid: false,
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
                },
                distritos: ['Aveiro', 'Beja', 'Braga', 'Bragança', 'Castelo Branco', 'Coimbra', 'Évora', 'Faro',
                    'Guarda', 'Leiria', 'Lisboa', 'Portalegre', 'Porto', 'Santarém', 'Setúbal', 'Viana do Castelo',
                    'Vila Real', 'Viseu', 'Açores', 'Madeira'],
                epocas: ['pré-história', 'idade antiga', 'idade média', 'idade contemporânea'],
                ciclos: ['1º ciclo', '2º ciclo', '3º ciclo', 'secundário'],
                patrimonio: {
                    nome: "",
                    descricao: "",
                    distrito: "",
                    epoca: "",
                    ciclo: ""
                },
            };
        },
        methods: {
            registar() {
                this.patrimonio.descricao = this.editorData;
                axios.post('/api/patrimonios', this.patrimonio).then(response=>{
                    this.toastPopUp("success", "Património Criado!");
                    this.$router.push('/admin/patrimonios');
                }).catch(function (error) {
                    this.toastPopUp("error", "Erro");
                    console.log(error);
                });
            },
            validate () {
                if (this.$refs.form.validate()) {
                    if (this.editorData === ""){
                        this.toastPopUp("error", "Descrição é obrigatória");
                    }
                    this.valid = true;
                    this.registar();
                }
            },
        }
    }
</script>
