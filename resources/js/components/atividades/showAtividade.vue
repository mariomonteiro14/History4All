<template>
    <div id="app">
        <br><br><br><br>

        <v-app id="inspire">
            <v-layout justify-center>
                <v-flex xs12 sm6>
                    <v-card>
                        <v-container fluid grid-list-md>
                            <v-flex v-if="atividade">
                                <v-layout row wrap>
                                    <v-toolbar flat>
                                        <v-toolbar-title>
                                            <h3 class="headline mb-0">{{atividade.titulo}}</h3>
                                            {{atividade.tipo}}
                                        </v-toolbar-title>
                                    </v-toolbar>
                                    <v-flex xs6>
                                        <v-card>
                                            <v-card-text>
                                                <span>{{atividade.descricao}}</span>
                                                <br><br><br><br>
                                                <p v-if="atividade.coordenador">Coordenador: {{atividade.coordenador.nome}}</p>
                                                Número de elementos por grupo: {{atividade.numeroElementos}}
                                                <div>
                                                    <v-btn small @click="showPatrimonios()">Patrimónios relacionados</v-btn>
                                                </div>
                                                <p v-if="podeParticipar">
                                                    <v-btn color="info">Participar</v-btn><span> - data de término: {{atividade.data}}</span>
                                                </p>
                                            </v-card-text>
                                            <br><br><br>
                                        </v-card>
                                        <br><br><br>
                                    </v-flex>
                                    <v-flex xs6>
                                        <v-card v-if="atividade.chat">
                                            <v-container fluid grid-list-md>
                                                <v-layout row wrap>
                                                    <v-list three-line>
                                                        {{atividade.chat.assunto}}
                                                        <template v-for="(mensagem, index) in atividade.chat.chat_mensagens">
                                                            <v-list-tile :key="index"avatar>
                                                                <v-list-tile-avatar>
                                                                    <img v-if="mensagem.user.foto" width="30px" height="30px" v-bind:src="getUserPhoto(mensagem.user.foto)"/>
                                                                </v-list-tile-avatar>
                                                                <v-list-tile-content>
                                                                    <v-list-tile-title v-html="mensagem.user.nome"></v-list-tile-title>
                                                                    <v-list-tile-sub-title v-html="mensagem.mensagem"></v-list-tile-sub-title>
                                                                </v-list-tile-content>
                                                            </v-list-tile>
                                                        </template>
                                                    </v-list>
                                                </v-layout>
                                            </v-container>
                                        </v-card>
                                    </v-flex>
                                    <br><br><br>
                                    <p>
                                        Testemunhos:
                                    </p>
                                </v-layout>
                            </v-flex>
                        </v-container>
                    </v-card>
                </v-flex>
            </v-layout>
        </v-app>
        <div class="modal fade" id="mostrarPatrimoniosModal" tabindex="-1" role="dialog" aria-labelledby="mostrarPatrimoniosModal"
             aria-hidden="true">
            <div class="modal-dialog modal-md" role="document">
                <div class="modal-content">
                    <div class="container box">
                        <div class="modal-header">
                            <h5 class="modal-title" id="mostrarPatrimoniosModal">Patrimónios envolvidos na atividade</h5>
                            <button type="button" class="close" data-dismiss="modal"
                                    aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <v-list class="form-group" subheader>
                            <v-list-tile v-if="atividade.patrimonios"
                                    v-for="patrimonio in atividade.patrimonios" :key="patrimonio.id" avatar
                            >
                                <v-list-tile-avatar>
                                    <img v-if="patrimonio.imagens && patrimonio.imagens[0]" :src="getPatrimonioPhoto(patrimonio.imagens[0])">
                                    <span v-else>{{patrimonio.nome[0]}}</span>
                                </v-list-tile-avatar>
                                <v-list-tile-content>
                                    <v-list-tile-title>{{patrimonio.nome}}</v-list-tile-title>
                                </v-list-tile-content>
                            </v-list-tile>
                        </v-list>
                    </div>
                </div>
            </div>
        </div>
    </div>

</template>
<script type="text/javascript">
    export default {
        props: ['id'],
        data: function () {
            return {
                atividade: [],
            };
        },
        methods: {
            getAtividade() {
                axios.get('/api/atividades/' + this.id)
                    .then(response => {
                        this.atividade = response.data;
                    })
                    .catch(errors => {
                        console.log(errors);
                    });
            },
            showPatrimonios(){
                $('#mostrarPatrimoniosModal').modal('show');
            }
        },
        computed:{
            podeParticipar(){
                if (this.$store.state.user.tipo == 'aluno' && this.atividade.participantes){
                     return this.atividade.participantes.findIndex(participante => participante.id === this.$store.state.user.id) == -1;
                }
                return false;
            }
        },
        mounted() {
            this.getAtividade();
        }
    }
</script>
