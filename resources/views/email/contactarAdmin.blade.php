<!DOCTYPE html>
<div>
    <h2>Assunto: {{$assunto}}</h2>
    <sub>
        <p style="font-size:18px">
            <b>De: {{$email}}</b>
        </p>
    </sub>
    <br>
    <h4>Mensagem:</h4>
    <v-container>
        <p style="font-size:18px">{{$texto}}</p>
    </v-container>
</div>
</html>
