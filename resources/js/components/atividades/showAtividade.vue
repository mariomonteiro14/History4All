<template>
    <div id="app">
        <br><br><br><br>
        <atividade-edit :atividade="atividadeBackup" v-on:atualizar="getAtividade()"></atividade-edit>
        <div v-if="isLoading">
            <br><br><br>
            <v-progress-linear v-slot:progress :color="colorDefault"
                               indeterminate></v-progress-linear>
            <br><br><br><br><br><br><br><br><br>
        </div>
        <v-app v-else id="inspire">

            <v-tabs fixed-tabs>
                <v-tab @click="tabSelecionada=0">Descricao</v-tab>
                <v-tab v-if="atividade.chat && participa" @click="tabSelecionada=1">Chat</v-tab>
                <v-tab v-if="!atividade.chat && participa" @click="tabSelecionada=1">Participantes</v-tab>
                <v-tab @click="tabSelecionada=2">Testemunhos</v-tab>
            </v-tabs>
            <v-container fluid grid-list-sm>
                <v-layout justify-center>
                    <v-flex xs12 sm12 md12>
                        <!--HEADER -->
                        <v-card v-if="atividade.titulo && tabSelecionada == 0">
                            <v-card-title primary-title>
                                <v-layout row wrap>
                                    <v-flex d-flex xs12 sm5>
                                        <v-flex d-flex xs12 sm10>
                                        <v-card flat>
                                            <br>
                                            <h3 class="display-1 font-weight-light">{{atividade.titulo}}</h3>
                                            <p class="title green--text font-weight-light">{{atividade.tipo}}</p>
                                        </v-card>
                                        </v-flex>
                                        <v-flex xs12 sm2>
                                            <v-btn icon v-if="$store.state.user.id==atividade.coordenador.id" flat data-toggle="modal"
                                                   data-target="#addAtividadeModal" @click="atividadeBackup = Object.assign({}, atividade)">
                                                <v-icon color="warning">edit</v-icon>
                                            </v-btn>
                                        </v-flex>
                                    </v-flex>

                                    <v-flex d-flex xs12 sm7 fluid>
                                        <v-layout justify-end row d-flex fluid>
                                            <v-spacer
                                                v-if="atividade.patrimonios && patrimoniosImagens.length < 4"></v-spacer>
                                            <v-spacer
                                                v-if="atividade.patrimonios && patrimoniosImagens.length < 3"></v-spacer>
                                            <v-spacer
                                                v-if="atividade.patrimonios && patrimoniosImagens.length < 2"></v-spacer>
                                            <v-flex class="text-xs-right"
                                                    v-for="(imagem, index) in patrimoniosImagens"
                                                    :key="index" d-flex>
                                                <v-img
                                                    :src="getPatrimonioPhoto(imagem)"
                                                    aspect-ratio="1"
                                                    class="grey lighten-2"
                                                    max-height="100"
                                                    max-width="100"
                                                >
                                                </v-img>
                                            </v-flex>
                                        </v-layout>
                                    </v-flex>
                                </v-layout>
                            </v-card-title>

                            <!--DESCRIÇAO-->
                            <v-divider></v-divider>

                            <v-card-text primary-title>
                                <span class=" font-weight-light grey--text">Descriçao:</span>
                                <h4 class="font-weight-light">{{atividade.descricao}}</h4>
                                <br>
                                <span class=" font-weight-light grey--text">Patrimonios:</span>
                                <v-layout>
                                    <h5 v-for="(patrimonio, index) in atividade.patrimonios" :key="index"
                                        class="blue--text font-weight-light">
                                        <a @click="$router.push('/patrimonio/'+ patrimonio.id)">
                                            {{patrimonio.nome}}&nbsp &nbsp
                                        </a>
                                    </h5>
                                </v-layout>
                                <br>
                                <v-divider></v-divider>
                                <br>
                                <v-layout>
                                    <v-flex>
                                        <span class="font-weight-light grey--text">Escola:</span>
                                        <h5>{{atividade.coordenador.escola[0]}}</h5>
                                    </v-flex>
                                    <v-flex>
                                        <span class="font-weight-light grey--text">Coordenador:</span>
                                        <h5 class="indigo--text darken-4">
                                            <a @click="$router.push('/users/'+ atividade.coordenador.id)">
                                                {{atividade.coordenador.nome}}
                                            </a>
                                        </h5>
                                    </v-flex>
                                    <v-spacer></v-spacer>
                                    <v-spacer></v-spacer>
                                    <v-flex>

                                        <v-btn flat round @click="showDetails = !showDetails">Mais Informações
                                            <v-icon>{{ !showDetails ? 'keyboard_arrow_down' : 'keyboard_arrow_up' }}
                                            </v-icon>
                                        </v-btn>
                                    </v-flex>
                                </v-layout>
                            </v-card-text>
                            <v-slide-y-transition>
                                <v-card-text v-show="showDetails">
                                    <v-divider></v-divider>
                                    <v-layout>
                                        <v-flex>
                                            <span class=" font-weight-light grey--text">Visibilidade:</span>
                                            <h6>{{atividade.visibilidade}}</h6>
                                        </v-flex>
                                        <v-flex>
                                            <span class=" font-weight-light grey--text">Numero de Participantes:</span>
                                            <h6>{{atividade.participantes.length}}</h6>
                                        </v-flex>
                                        <v-flex>
                                            <span class=" font-weight-light grey--text">Numero de elementos por grupo:</span>
                                            <h6>{{atividade.numeroElementos}}</h6>
                                        </v-flex>
                                        <v-flex>
                                            <span v-if="!atividade.dataFinal" class=" font-weight-light grey--text">Data da Atividade:</span>
                                            <span v-else class=" font-weight-light grey--text">Data de Inicio:</span>
                                            <h6>{{atividade.dataInicio}}</h6>
                                        </v-flex>
                                        <v-flex v-if="atividade.dataFinal">
                                            <span class=" font-weight-light grey--text">Data de conclusao:</span>
                                            <h6>{{atividade.dataFinal}}</h6>
                                        </v-flex>
                                    </v-layout>
                                    <br>
                                    <v-layout>
                                        <v-flex>
                                            <span class=" font-weight-light grey--text">Epocas:</span>
                                            <v-layout row>
                                                <h6 v-for="(epoca, index) in atividade.epoca" :key="index">
                                                    {{epoca}}&nbsp &nbsp</h6>
                                            </v-layout>
                                        </v-flex>
                                        <v-flex>
                                            <span class=" font-weight-light grey--text">Ciclos:</span>
                                            <v-layout row>
                                                <h6 v-for="(ciclo, index) in atividade.ciclo" :key="index">{{ciclo}}
                                                    &nbsp &nbsp</h6>
                                            </v-layout>
                                        </v-flex>
                                    </v-layout>
                                </v-card-text>
                            </v-slide-y-transition>
                        </v-card>
                        <br>
                        <!-- CHAT -->
                        <v-card v-if="participa && tabSelecionada == 1">
                            <v-card-text primary-title>
                                <v-layout row wrap>
                                    <v-flex d-flex xs12 sm8 v-if="atividade.chat">
                                        <v-card flat>
                                            <v-card-text>
                                                <h5 class="blue--text">
                                                    <v-icon class="blue--text">far fa-comments</v-icon>
                                                    &nbsp Chat
                                                </h5>
                                                <div v-if="atividade.chat.assunto">
                                                <v-divider></v-divider>
                                                <span class=" font-weight-light grey--text">Assunto:</span>
                                                <h6>{{atividade.chat.assunto}}</h6>
                                                </div>
                                                <v-divider></v-divider>
                                                <v-flex xs12 id="scroll-target"
                                                        style="max-height:350px; min-height:250px; overflow-y:auto">
                                                    <v-layout row wrap v-scroll:#scroll-target="onScroll">
                                                        <div id="scrolled-content">
                                                            <v-list three-line>
                                                                <span class="grey--text"
                                                                      v-if="mensagensDoChat.length == 0">(Não existem mensagens)</span>
                                                                <template v-for="(mensagem, index) in mensagensDoChat">
                                                                    <v-list-tile :key="index" avatar>
                                                                        <v-list-tile-avatar>
                                                                            <img v-if="mensagem.user.foto" width="30px"
                                                                                 height="30px"
                                                                                 v-bind:src="getUserPhoto(mensagem.user.foto)"/>
                                                                            <v-icon v-else class="indigo--text" small>
                                                                                far fa-user
                                                                            </v-icon>
                                                                        </v-list-tile-avatar>
                                                                        <v-list-tile-content>
                                                                            <v-list-tile-title>
                                                                                {{mensagem.user.nome}}</v-list-tile-title>
                                                                            <v-list-tile-sub-title>
                                                                                {{mensagem.mensagem}}</v-list-tile-sub-title>
                                                                        </v-list-tile-content>
                                                                    </v-list-tile>
                                                                </template>
                                                            </v-list>
                                                        </div>
                                                    </v-layout>
                                                </v-flex>
                                            </v-card-text>
                                            <v-card-actions>
                                                <v-layout row wrap>

                                                    <v-flex xs11>
                                                        <v-text-field
                                                            v-model="mensagemAEnviar"
                                                            outline
                                                            clearable
                                                            label="Mensagem"
                                                            type="text"
                                                            @click:clear="mensagemAEnviar=''"
                                                            @keyup.enter="enviarMensagem"
                                                        ></v-text-field>
                                                    </v-flex>
                                                    <v-flex xs1>
                                                        <v-btn icon large v-if="mensagemAEnviar != ''"
                                                               @click="enviarMensagem">
                                                            <v-icon class="blue--text" large>send</v-icon>
                                                        </v-btn>
                                                    </v-flex>

                                                </v-layout>
                                            </v-card-actions>
                                        </v-card>
                                    </v-flex>
                                    <v-divider v-if="atividade.chat" vertical></v-divider>
                                    <v-flex d-flex xs12 :class="atividade.chat ? 'sm3': 'sm12'" fluid class="justify-right">
                                        <v-card flat>
                                            <v-card-text>
                                                <h5 class="orange--text font-weight-light">
                                                    <v-icon class="orange--text">far fa-user</v-icon>
                                                    <v-icon class="orange--text">fas fa-user</v-icon>
                                                    &nbsp Participantes:
                                                </h5>

                                                <v-divider></v-divider>
                                                <v-flex style="max-height:350px; min-height:100px; overflow-y:auto">
                                                    <v-list class="form-group" subheader>

                                                        <v-list-tile
                                                            v-for="aluno in atividade.participantes"
                                                            :key="aluno.title"
                                                            avatar
                                                        >
                                                            <v-list-tile-avatar>
                                                                <img v-if="aluno.foto" :src="getUserPhoto(aluno.foto)">
                                                                <v-icon v-else class="indigo--text" small>far fa-user
                                                                </v-icon>
                                                            </v-list-tile-avatar>

                                                            <v-list-tile-content>
                                                                <v-list-tile-title class="indigo--text darken-4">
                                                                    <span v-if="aluno.id == $store.state.user.id">Eu</span>
                                                                    <a v-else @click="$router.push('/users/'+ aluno.id)">
                                                                        {{atividade.chat ? getPrimeiroUltimoNome(aluno.nome) : aluno.nome}}
                                                                    </a>
                                                                </v-list-tile-title>
                                                            </v-list-tile-content>
                                                        </v-list-tile>
                                                    </v-list>

                                                </v-flex>
                                                <v-btn v-if="atividade.coordenador.id === $store.state.user.id" color="orange"
                                                       round class="white--text" @click="enviarNotificacao">
                                                    Notificar &nbsp
                                                    <v-icon small>send</v-icon>
                                                </v-btn>
                                            </v-card-text>
                                        </v-card>
                                    </v-flex>
                                </v-layout>
                            </v-card-text>
                        </v-card>
                        <br>
                        <!--TESTEMUNHOS-->
                        <v-card v-if="tabSelecionada == 2">
                            <v-card-title primary-title>
                                <div>
                                    <h4>Testemunhos:</h4>
                                </div>
                                <v-spacer></v-spacer>
                                <loader v-if="loadingTestemunho" color="green" size="50px"></loader>
                            </v-card-title>
                            <div v-if="!loadingTestemunho">
                                <v-card-text>
                                    <h6 v-if="testemunhosValidados.length == 0" class="grey--text"> Nao existem
                                        testemunhos</h6>
                                    <v-data-table v-else
                                                  :items="testemunhosValidados"
                                                  class="elevation-1"
                                                  hide-actions
                                                  hide-headers
                                                  style="max-height:200px; min-height:50px; overflow-y:auto"
                                    >
                                        <template v-slot:items="props">
                                            <td class="text-xs-left">
                                                <v-icon class="indigo--text" small>far fa-user</v-icon>
                                            </td>

                                            <td class="text-xs-left" style="max-width:400px; min-width:400px;">
                                                <div
                                                    v-if="!(myTestemunho.user_id && myTestemunho.user_id == props.item.user_id)">
                                                    {{ props.item.texto }}
                                                </div>
                                                <v-text-field v-else
                                                              v-model="myTestemunho.texto"
                                                              clearable
                                                              placeholder="Testemunho"
                                                              :color="colorDefault"
                                                              :rules="[v => !!v || 'Testemunho é obrigatório']"
                                                              required
                                                              @keyup.enter="saveEditTestemunho"
                                                ></v-text-field>

                                            </td>
                                            <td class="text-xs-right">
                                                <v-rating
                                                    v-if="!(myTestemunho.user_id && myTestemunho.user_id == props.item.user_id)"
                                                    v-model="props.item.rate"
                                                    readonly
                                                    :background-color="colorDefault"
                                                    :color="colorDefault"
                                                    small
                                                ></v-rating>
                                                <v-rating v-else
                                                          v-model="myTestemunho.rate"
                                                          :background-color="colorDefault"
                                                          :color="colorDefault"
                                                          hover
                                                ></v-rating>
                                            </td>
                                            <td class="text-xs-right" v-if="props.item.user_id == $store.state.user.id">
                                                <v-btn
                                                    v-if="!(myTestemunho.user_id && myTestemunho.user_id == props.item.user_id)"
                                                    icon color="warning" @click="showEditTestemunho(props.item)">
                                                    <v-icon small>edit</v-icon>
                                                </v-btn>
                                                <v-btn
                                                    v-if="(myTestemunho.user_id && myTestemunho.user_id == props.item.user_id)"
                                                    icon color="success" @click="saveEditTestemunho()"
                                                    :disabled="myTestemunho.texto == props.item.texto && myTestemunho.rate == props.item.rate">
                                                    <v-icon small>check</v-icon>
                                                </v-btn>
                                                <v-btn
                                                    v-if="(myTestemunho.user_id && myTestemunho.user_id == props.item.user_id)"
                                                    icon color="warning" @click="cancelEdit">
                                                    <v-icon small>close</v-icon>
                                                </v-btn>
                                                <v-btn color="error" icon
                                                       @click.stop="apagarTestemunho(props.item)">
                                                    <v-icon small>delete_forever</v-icon>
                                                </v-btn>
                                            </td>
                                            <td class="text-xs-right" v-else></td>
                                        </template>
                                    </v-data-table>
                                </v-card-text>
                                <v-divider v-if="testemunhosNaoValidados.length > 0"></v-divider>
                                <!--TESTEMUNHOS NAO VALIDADOS -->
                                <v-container fluid grid-list-sm v-if="testemunhosNaoValidados.length > 0">
                                    <v-card>
                                        <v-card-title primary-title>
                                            <div v-if="atividade.coordenador.id == $store.state.user.id">
                                                <h4>Validar Testemunhos:</h4>
                                            </div>
                                            <div v-else>
                                                <h4>O meu testemunho:</h4>
                                                <span>(A espera de ser validado pelo coordenador)</span>
                                            </div>
                                        </v-card-title>
                                        <v-card-text>
                                            <v-data-table
                                                :items="testemunhosNaoValidados"
                                                class="elevation-1"
                                                hide-actions
                                                hide-headers
                                                style="max-height:200px; min-height:50px; overflow-y:auto"
                                            >
                                                <template v-slot:items="props">
                                                    <td class="text-xs-left">
                                                        <v-icon class="indigo--text" small>far fa-user</v-icon>
                                                    </td>

                                                    <td class="text-xs-left"
                                                        style="max-width:400px; min-width:400px;">
                                                        <div
                                                            v-if="!(myTestemunho.user_id && myTestemunho.user_id == props.item.user_id)">
                                                            {{ props.item.texto }}
                                                        </div>
                                                        <v-text-field v-else
                                                                      v-model="myTestemunho.texto"
                                                                      clearable
                                                                      placeholder="Testemunho"
                                                                      :color="colorDefault"
                                                                      :rules="[v => !!v || 'Testemunho é obrigatório']"
                                                                      required
                                                                      @keyup.enter="saveEditTestemunho"
                                                        ></v-text-field>

                                                    </td>
                                                    <td class="text-xs-right">
                                                        <v-rating
                                                            v-if="!(myTestemunho.user_id && myTestemunho.user_id == props.item.user_id)"
                                                            v-model="props.item.rate"
                                                            readonly
                                                            :background-color="colorDefault"
                                                            :color="colorDefault"
                                                            small
                                                        ></v-rating>
                                                        <v-rating v-else
                                                                  v-model="myTestemunho.rate"
                                                                  :background-color="colorDefault"
                                                                  :color="colorDefault"
                                                                  hover
                                                        ></v-rating>
                                                    </td>
                                                    <td class="text-xs-right" v-if="props.item.user_id == $store.state.user.id ||
                                                    ($store.state.user.id === atividade.coordenador.id && props.item.confirmado === 0)">
                                                        <v-btn
                                                            v-if="!(myTestemunho.user_id && myTestemunho.user_id == props.item.user_id) &&
                                                         props.item.user_id == $store.state.user.id"
                                                            icon color="warning"
                                                            @click="showEditTestemunho(props.item)">
                                                            <v-icon small>edit</v-icon>
                                                        </v-btn>
                                                        <v-btn
                                                            v-if="(myTestemunho.user_id && myTestemunho.user_id == props.item.user_id) &&
                                                        props.item.user_id == $store.state.user.id"
                                                            icon color="success" @click="saveEditTestemunho()"
                                                            :disabled="!myTestemunho.texto || myTestemunho.texto == props.item.texto && myTestemunho.rate == props.item.rate">
                                                            <v-icon small>check</v-icon>
                                                        </v-btn>
                                                        <v-btn
                                                            v-if="(myTestemunho.user_id && myTestemunho.user_id == props.item.user_id) &&
                                                         props.item.user_id == $store.state.user.id"
                                                            icon color="warning" @click="cancelEdit">
                                                            <v-icon small>close</v-icon>
                                                        </v-btn>
                                                        <v-btn
                                                            v-if="atividade.coordenador.id == $store.state.user.id && props.item.confirmado === 0"
                                                            icon color="success"
                                                            @click="confirmarTestemunho(props.item)">
                                                            <v-icon small>check</v-icon>
                                                        </v-btn>
                                                        <v-btn color="error" icon
                                                               @click.stop="apagarTestemunho(props.item)">
                                                            <v-icon small>delete_forever</v-icon>
                                                        </v-btn>
                                                    </td>
                                                    <td class="text-xs-right" v-else></td>
                                                </template>
                                            </v-data-table>
                                        </v-card-text>
                                    </v-card>
                                </v-container>
                                <v-divider></v-divider>
                                <v-card-actions v-if="canWriteTestemunho && participa">
                                    <v-container fluid grid-list-sm>
                                        <a @click="showEscrever = !showEscrever" class="indigo--text">
                                            <h5>Escrever Testemunho</h5></a>
                                        <v-slide-y-transition>
                                            <v-layout v-show="showEscrever">
                                                <v-flex sm8>
                                                    <v-text-field
                                                        v-model="myTestemunho.texto"
                                                        clearable
                                                        placeholder="Testemunho"
                                                        :color="colorDefault"
                                                        :rules="[v => !!v || 'Testemunho é obrigatório']"
                                                        required
                                                        @keyup.enter="enviarTestemunho"
                                                    ></v-text-field>
                                                </v-flex>
                                                <v-spacer></v-spacer>
                                                <v-flex sm2>
                                                    <v-rating
                                                        v-model="myTestemunho.rate"
                                                        :background-color="colorDefault"
                                                        :color="colorDefault"
                                                        medium
                                                        hover
                                                    ></v-rating>
                                                </v-flex>
                                                <v-flex sm1>
                                                    <v-btn flat v-if="myTestemunho.texto && myTestemunho.rate"
                                                           :color="colorDefault"
                                                           round class="white--text" @click="enviarTestemunho">
                                                        &nbsp
                                                        <v-icon large>send</v-icon>
                                                    </v-btn>
                                                </v-flex>
                                            </v-layout>
                                        </v-slide-y-transition>
                                    </v-container>
                                </v-card-actions>
                            </div>
                        </v-card>
                    </v-flex>
                </v-layout>
                <v-layout align-content-center>
                    <v-flex xs12 sm12 md12>
                        <v-btn class="primary--text subheading" round flat @click="$router.go(-1)">
                            <v-icon>fa fa-arrow-left</v-icon>
                            &nbsp Voltar
                        </v-btn>
                    </v-flex>
                </v-layout>
            </v-container>
        </v-app>

        <br>
        <div class="modal fade" id="mostrarPatrimoniosModal" tabindex="-1" role="dialog"
             aria-labelledby="mostrarPatrimoniosModal"
             aria-hidden="true">
            <div class="modal-dialog modal-md" role="document">
                <div class="modal-content">
                    <div class="container box">
                        <div class="modal-header">
                            <h5 class="modal-title">Patrimónios envolvidos na atividade</h5>
                            <button type="button" class="close" data-dismiss="modal"
                                    aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <v-list class="form-group" subheader v-if="atividade.patrimonios">
                            <v-list-tile v-for="patrimonio in atividade.patrimonios" :key="patrimonio.id" avatar>
                                <v-list-tile-avatar>
                                    <img v-if="patrimonio.imagens && patrimonio.imagens[0]"
                                         :src="getPatrimonioPhoto(patrimonio.imagens[0])">
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
        <enviar-notificacao ref="enviarNotificacaoModal" :atividadeTitulo="atividade.titulo" :atividadeId="atividade.id"
                            :users="atividade.participantes"></enviar-notificacao>
    </div>
</template>
<script type="text/javascript">
    import BRow from "bootstrap-vue/src/components/layout/row";
    import editarAtividade from './adicionarEditarAtividade.vue';
    import enviarNotificacao from '../widgets/enviarNotificacao';

    export default {
        components: {
            BRow,
            'enviar-notificacao': enviarNotificacao,
            'atividade-edit': editarAtividade
        },
        props: ['id'],
        data: function () {
            return {
                atividade: {},
                mensagensDoChat: [],
                offset: 4,
                mensagemAEnviar: '',

                testemunhos: [],
                numeroImagens: 0,
                showDetails: false,
                isLoading: true,
                actualScroll: 0,
                myTestemunho: {
                    rate: 3
                },
                testemunhoValido: true,
                loadingTestemunho: false,
                showEscrever: false,
                tabSelecionada: 0,
                atividadeBackup:{}
            };
        },
        methods: {
            getAtividade() {
                this.isLoading = true;
                axios.get('/api/atividades/' + this.id)
                    .then(response => {
                        this.atividade = response.data.data;
                        if (this.atividade.chat) {
                            let mensagens = this.atividade.chat.chat_mensagens;
                            this.mensagensDoChat = mensagens.slice(mensagens.length - this.offset);
                            this.$socket.emit('user_enter_chat', this.$store.state.user, this.atividade.chat.id);
                        }
                        this.testemunhos = this.atividade.testemunhos;
                        this.isLoading = false;
                    }).catch(error => {
                    this.isLoading = false;
                    this.toastErrorApi(error);
                    this.$router.push('/atividades');

                });
            },
            showPatrimonios() {
                $('#mostrarPatrimoniosModal').modal('show');
            },
            enviarNotificacao() {
                $('#enviarNotificacaoModal').modal('show');
            },
            participar() {
                axios.post('/api/atividades/' + this.id + '/participar', {'user_id': this.$store.state.user.id}).then(response => {
                    if (this.atividade.chat) {
                        this.$socket.emit('user_enter_chat', this.$store.state.user, this.atividade.chat.id);
                    }
                    this.toastPopUp("success", "A atividade encontra-se na sua lista de atividades pendentes!");
                }).catch(error => {
                    this.toastErrorApi(error);
                })
            },
            enviarMensagem() {
                let chatMensagem = {
                    'chat_id': this.atividade.chat.id,
                    'mensagem': this.mensagemAEnviar,
                    'user_id': this.$store.state.user.id
                };
                this.mensagemAEnviar = '';
                axios.post('/api/chat', chatMensagem).then(response => {
                    this.$socket.emit('chat_mensagem', response.data, this.atividade.chat.id);
                }).catch(error => {
                    this.toastErrorApi(error);
                })
            },
            getTestemunhos() {
                this.loadingTestemunho = true;
                axios.get('/api/atividade/' + this.atividade.id + '/testemunhos').then(response => {
                    this.testemunhos = response.data;
                    this.loadingTestemunho = false;
                }).catch(error => {
                    this.loadingTestemunho = false;
                    this.toastErrorApi(error);
                })
            },
            enviarTestemunho() {
                if (this.myTestemunho.texto && this.myTestemunho.rate) {
                    this.myTestemunho.atividade_id = this.atividade.id;
                    this.myTestemunho.user_id = this.$store.state.user.id;
                    this.loadingTestemunho = true;
                    axios.post('/api/atividade/' + this.atividade.id + '/testemunho', this.myTestemunho).then(response => {
                        this.myTestemunho = {rate: 3};
                        this.showEscrever = false;
                        if (this.$store.state.user.id === this.atividade.coordenador.id) {
                            this.toastPopUp("success", "Testemunho registado");
                        } else {
                            this.toastPopUp("success", "Testemunho registado, ficará visivel assim que o coordenador da atividade o validar");
                        }
                        this.getTestemunhos();
                        this.$socket.emit('atualizar_notificacoes', this.atividade.coordenador.id);
                    }).catch(error => {
                        this.loadingTestemunho = false;
                        this.toastErrorApi(error);
                    })
                }

            },
            showEditTestemunho(testemunho) {
                this.myTestemunho = Object.assign({}, testemunho);
            },
            cancelEdit() {
                this.myTestemunho = {rate: 3};
            },
            saveEditTestemunho() {
                this.loadingTestemunho = true;
                axios.put('/api/atividade/' + this.atividade.id + '/testemunho', this.myTestemunho).then(response => {
                    this.myTestemunho = {rate: 3};
                    this.getTestemunhos();
                    if (this.$store.state.user.id === this.atividade.coordenador.id) {
                        this.toastPopUp("success", "Testemunho atualizado");
                    } else {
                        this.toastPopUp("success", "Testemunho atualizado, ficará visivel assim que o coordenador da atividade o validar");
                    }
                    this.loadingTestemunho = false;
                    this.$socket.emit('atualizar_notificacoes', this.atividade.coordenador.id);
                }).catch(error => {
                    this.loadingTestemunho = false;
                    this.toastErrorApi(error);
                })
            },
            confirmarTestemunho(testemunho) {
                this.loadingTestemunho = true;
                axios.put('/api/atividade/' + this.atividade.id + '/testemunho/' + testemunho.user_id).then(response => {
                    this.toastPopUp("success", "Testemunho visível");
                    this.getTestemunhos();
                    this.loadingTestemunho = false;
                    this.$socket.emit('atualizar_notificacoes', testemunho.user_id);
                }).catch(error => {
                    this.loadingTestemunho = false;
                    this.toastErrorApi(error);
                })
            },
            apagarTestemunho(testemunho) {
                this.loadingTestemunho = true;
                axios.delete('/api/atividade/' + this.atividade.id + '/testemunho/' + testemunho.user_id).then(response => {
                    this.toastPopUp("success", "Testemunho apagado");
                    this.getTestemunhos();
                    this.loadingTestemunho = false;
                    this.$socket.emit('atualizar_notificacoes', testemunho.user_id);
                }).catch(error => {
                    this.loadingTestemunho = false;
                    this.toastErrorApi(error);
                })

            },
            onScroll(e) {
                if (this.actualScroll < e.target.scrollTop) {
                    this.scrollDown();
                }
                if (e && e.target.scrollTop === 0 && this.atividade.chat.chat_mensagens.length !== this.mensagensDoChat.length) {
                    this.offset += 20;
                    this.toastPopUp("success", "Mais mensagens carregadas");
                    let mensagens = this.atividade.chat.chat_mensagens;
                    this.mensagensDoChat = mensagens.slice(mensagens.length - this.offset);
                }
                this.actualScroll = e.target.scrollTop;
            },
            scrollDown() {
                document.getElementById("scroll-target").scrollTop = document.getElementById("scrolled-content").offsetHeight;
            }
        },
        mounted() {
            this.getAtividade();
        },
        computed: {
            patrimoniosImagens() {
                let imagens = [];
                let numeroMaxImgs = 5;
                let numImgPorPatrim = 1;

                if (this.atividade && this.atividade.patrimonios) {
                    if (this.atividade.patrimonios.length === 1) {
                        numImgPorPatrim = 5
                    }
                    else if (this.atividade.patrimonios.length <= 3) {
                        numImgPorPatrim = 2;
                    }
                    this.atividade.patrimonios.forEach(function (element) {
                        let count = 1;
                        element.imagens.forEach(function (elem) {

                            if (count <= numImgPorPatrim) {
                                imagens.push(elem);
                                count++;
                                if (imagens[numeroMaxImgs - 1]) {
                                    return imagens;
                                }
                            }
                        });
                    });

                }
                return imagens;
            },
            testemunhosValidados() {
                if (this.testemunhos.length == 0) {
                    return [];
                }
                return this.testemunhos.filter((i) => {
                    return (i.confirmado == 1);
                });
            },
            testemunhosNaoValidados() {
                if (this.testemunhos.length == 0) {
                    return [];
                }
                if (this.$store.state.user.tipo == "aluno") {
                    let participa = false;
                    let me = this.$store.state.user;
                    this.atividade.participantes.forEach(function (element) {
                        if (element.id == me.id) {
                            participa = true;
                            return;
                        }
                    });
                    if (participa) {
                        return this.testemunhos.filter((i) => {
                            return (i.confirmado == 0 && i.user_id == me.id);
                        });
                    }
                }
                if (this.$store.state.user.id == this.atividade.coordenador.id) {
                    return this.testemunhos.filter((i) => {
                        return (i.confirmado == 0);
                    });
                }
                return [];
            },
            participa() {
                let me = this.$store.state.user;
                if (this.atividade.coordenador.email === me.email) {
                    return true;
                }
                let resp = false;
                this.atividade.participantes.forEach(function (element) {
                    if (element.id == me.id) {
                        resp = true;
                        return;
                    }
                });
                return resp;
            },
            canWriteTestemunho() {
                let me = this.$store.state.user;
                let resp = true;
                this.testemunhos.forEach(function (element) {
                    if (element.user_id === me.id) {
                        resp = false;
                        return;
                    }
                });
                return resp;
            },
        },
        sockets: {
            connect() {
                if (this.atividade.chat) {
                    this.$socket.emit('user_enter_chat', this.$store.state.user, this.atividade.chat.id);
                }
            },
            novaMensagem(mensagem) {
                this.atividade.chat.chat_mensagens.push(mensagem);
                this.mensagensDoChat.push(mensagem);
                this.scrollDown();
            }
        },
        destroyed: function () {
            if (this.atividade.chat && this.$store.state.user) {
                this.$socket.emit('user_exit_chat', this.$store.state.user, this.atividade.chat.id);
            }
        },
    }
</script>

