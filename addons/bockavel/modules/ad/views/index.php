

<div class="panel article">
    <div class="panel-body">
        <h1>{{ template:title }}</h1>

        {{ snippets:adtext }}




<!--
        <p>&nbsp;</p>
        <p>Här skapar du och redigerar dina privatannonser. En annons visas i två månader från det att den senast skapades eller uppdaterades.  </p>
        <p>När du skapat en annons är den publik för alla som besöker sidan. Inga personliga uppgifter visas i samband med annonsen. Väljer någon att kontakta dig så skickas ett svar med kontaktinformation till din E-post ({{ user:email }})</p>
-->
    </div>


        <div class="panel-body" style="padding-bottom:0px;padding-top:0px;">
            <a class="btn btn-default pull-right" href="ad/create" role="button">Skapa ny annons</a>
        </div>

    <div class="panel-body">

        <div class="table-responsive">



            <table class="table">
                <thead>
                <tr>

                    <th>Datum</th>
                    <th>Rubrik</th>
                    <th></th>

                </tr>
                </thead>
                <tbody>
                {{ streams:cycle stream="ads" limit="500" restrict_user="current"  no_results="<tr><td colspan='2'><br><p>Du har inga annonser. <a href='ad/create'> Skapa en annons.</a></p></td></tr></div>" }}
                <tr>
                <td><a href="ad/show/{{ id }}">{{ helper:date timestamp=updated }}</a></td>
                    <td style="width:70%;"><a href="ad/show/{{ id }}/ad">{{ ad_title }}</a></td>
                    <td style="width:60px;padding-right:0px;">
                        <a href="ad/edit/{{ id }}"><?php echo Asset::img('icon-edit.jpeg', 'Redigera annons', ['width' => 20]); ?></a>&nbsp;
                        <a href="ad/delete/{{ id }}"  onclick="return confirm('Vill du verkligen radera annonsen?')"><?php echo Asset::img('icon-delete.jpeg', 'Radera annons', ['width' => 20]); ?></a>
                </td>
                </tr>
                {{ /streams:cycle }}
                </tbody>
            </table>


        </div>



    </div>




</div>
<script>

</script>

