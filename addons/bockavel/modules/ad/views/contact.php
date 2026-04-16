<style>
.form-group img{
    padding-bottom:12px;
    margin-left:-8px;

}

select[name="ad_type"]{
    width:40%;
}

input[name="ad_amount"]{
    width:20%;
}
input[type="file"] {
    width:50%;
}

</style>

<div class="panel article">
    <div class="panel-body">
        <h1>{{ template:title }}</h1>
    </div>




    <div class="panel-body">

        {{ streams:form success_message="Meddelande skickat" return="ad" stream="ad_contacts" mode="new" }}

        {{ form_open }}

            {{ fields }}


            <div class="form-group">
                <label for="inputfield">{{ input_title }}</label>&nbsp;<small>{{ instructions }}</small>
                <p class="text-warning">{{ error }}</p>{{ input }}
            </div>
            {{ /fields }}

        {{ form_submit }}

        {{ form_close }}

        {{ /streams:form }}
        <a class="btn btn-default pull-right" style="margin-right:10px;" href="ad/index" role="button">Tillbaka</a>
        {{ streams:form_assets }}





    </div>


</div>
<script>
    $(function() {
        $('.form-group > input, .form-group > textarea ').addClass('form-control');
        $('form >  input[type="submit"]').addClass('btn btn-success pull-right');
        $('form >  input[type="submit"]').attr('value','Spara annons');
    });
</script>

