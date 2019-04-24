<template>
    <div>
        <br><br><br><br><br>
        <link href="/css/profile.css" rel="stylesheet" type="text/css">
        <br>
        <div class="container emp-profile">
            <div class="row">
                <div class="col-md-4">
                    <div class="profile-img">
                        <v-avatar size="150px">
                            <img v-if="profileUser.foto != null" v-bind:src="getUserPhoto(profileUser.foto)" alt=""/>
                        </v-avatar>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="profile-head">
                        <h5>
                            {{profileUser.nome}}
                        </h5>
                        <h6>
                            {{profileUser.tipo.charAt(0).toUpperCase() + profileUser.tipo.slice(1)}}
                        </h6>
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab"
                                   aria-controls="home" aria-selected="true">Sobre</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-2">
                    <button class="profile-edit-btn" name="btnAddMore" href="#editProfileModal" data-toggle="modal"
                            data-target="#editProfileModal">Editar Perfil
                    </button>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="profile-work">
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="tab-content profile-tab" id="myTabContent">
                        <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                            <div class="row">
                                <div class="col-md-6">
                                    <label>Nome</label>
                                </div>
                                <div class="col-md-6">
                                    <p>{{profileUser.nome}}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label>Email</label>
                                </div>
                                <div class="col-md-6">
                                    <p>{{profileUser.email}}</p>
                                </div>
                            </div>
                            <div v-if="profileUser.tipo != 'admin'" class="row">
                                <div class="col-md-6">
                                    <label>Escola</label>
                                </div>
                                <div class="col-md-6">
                                    <p>{{profileUser.escola[0]}}</p>
                                </div>
                            </div>
                            <div v-if="profileUser.tipo == 'aluno'" class="row">
                                <div class="col-md-6">
                                    <label>Turma</label>
                                </div>
                                <div class="col-md-6">
                                    <p>{{profileUser.turma[0]}}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <edit-profile @reloadUser="profileUser = $store.state.user"></edit-profile>
    </div>
</template>
<script type="text/javascript">
    import editProfile from './editPerfil.vue';

    export default {
        components: {
            'edit-profile': editProfile,
        },
        data: function () {
            return {
                profileUser: this.$store.state.user,
                successMessage: "",
                showSuccess: false
            }
        },
        methods: {},
    }
</script>
