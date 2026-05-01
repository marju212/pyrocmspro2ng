

<div class="panel article">


    <?php if (isset($contact)): ?>
        <style>

            input[name="ad_contact_zip"]{
                width:20%;
            }

            input[name="ad_contact_zip_location"]{
                width:40%;
            }
            input[name="ad_contact_address"],input[name="ad_contact_phone"],input[name="ad_contact_cellular"] {
                width:50%;
            }
            input[name="ad_contact_se_number"] {
                width:40%;
            }
            input[name="ad_contact_mail"],input[name="ad_contact_name"] {
                 width:80%;
             }
        </style>

        <div class="panel-body">
            <h2 style="margin-top:10px;padding-top:0px;padding-bottom:30px;">Kontakta annonsören</h2>

            {{ snippets:adcontact }}

            {{ streams:form  exclude="ad_id|ad_contact_heading" required="*" success_message="Meddelande skickat" return="ad/show/all" stream="ad_contacts" mode="new" notify_a="<?php echo $ad_creator ?>" notify_template_a="ad_contact" notify_from_a="noreply@svenskagetavelsforbundet.se"}}

            {{ form_open }}

            {{ fields }}


            <div class="form-group">
                <label for="inputfield">{{ input_title }}<span>{{required}}</span> <span class="text-danger">{{ error }}</span></label>&nbsp;<small>{{ instructions }}</small>
                {{ input }}
            </div>
            {{ /fields }}
            {{# recaptcha_error #}}
            {{# recaptcha #}}

            <input type="hidden" name="ad_id" value="<?php echo $id; ?>">
            {{ Ad:spamFilter }}
            <span class="pull-left">* Obligatoriska fält</span>
            <input type="submit" value="Skicka meddelande" class="btn btn-success pull-right">
            {{ form_close }}


            {{ /streams:form }}
            <a class="btn btn-default pull-right" style="margin-right:10px;" href="ad/show/{{ url:segments segment='3' }}" role="button">Ångra</a>
            {{ streams:form_assets }}





        </div>


    <?php endif; ?>


    <div class="panel-body">


        {{ streams:cycle stream="ads" where="`id`='<?php echo $id; ?>'" }}





        <div class="row">
            <div class="col-sm-12" >
                <h5 class="media-heading text-muted">{{ helper:date timestamp=updated }} {{ ad_type:title }}{{ if ad_location }}, {{ ad_location }}{{ endif }}</h5>
                <h3 style="padding-bottom:22px;">{{ ad_title }}</h3>

            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <div class="row">
                    {{ if ad_image_1 }}
                    <div class="col-sm-12" style="padding-bottom:10px;">
                        <a href="{{ ad_image_1:image }}" id="img1" target="_blank"><img src="{{ ad_image_1:image }}" class="img-thumbnail main-img" style="width:100%;"/></a>
                    </div>
                    {{ endif }}
                    <div class="col-sm-12">

                        {{ if ( ad_image_1 && ( ad_image_2 || ad_image_3 ) ) }}
                        <img src="{{ ad_image_1:image }}" class="img-thumbnail small-img" style="max-width:112px;"/>
                        {{ endif }}
                        {{ if ad_image_2 }}
                        <img src="{{ ad_image_2:image }}" class="img-thumbnail small-img" style="max-width:112px;"/>
                        {{ endif }}
                        {{ if ad_image_3 }}
                        <img src="{{ ad_image_3:image }}" class="img-thumbnail small-img" style="max-width:112px;"/>
                        {{ endif }}




                    </div>
                    {{ if ad_pdf_file:filename }}
                    <div class="col-sm-12">
                        <p style="padding-top:5px;"><a href="{{ ad_pdf_file:file }}"><?php echo Asset::img('icon-pdf.jpeg', 'Redigera annons', ['width' => 80]); ?> Ladda hem information</a></p>
                    </div>
                    {{ endif }}
                </div>
            </div>
            <div class="col-sm-6"><p>{{ ad:nl2br data="{{ ad_body }}" }}</p>
                <div class="row">
                    {{ if ad_amount }}
                    <div class="col-sm-12 pull-right">
                        <div class="pull-right" style="padding-top:22px;font-weight:bold;"><p class="text-success">{{ if ad_amount }}Pris {{ ad_amount }} kr {{ endif }}</p></div>
                    </div>
                    {{ endif }}
                </div>
            </div>
        </div>
        <div class="row">
            {{ if { url:segments segment="2" }!="contact" }}
            <div class="col-sm-12" >
                <a class="btn btn-success pull-right" href="ad/contact/{{ id }}" role="button">Kontakta annonsören</a>
                <a class="btn btn-default pull-right" style="margin-right:10px;" href="{{ url:segments segment="4" default="ad/show/all" }}" role="button">Tillbaka</a>

            </div>
            {{ endif }}
        </div>
        {{ /streams:cycle }}
    </div>


</div>

<script>

    $(function() {
        $('.form-group > input, .form-group > textarea ').addClass('form-control');
        $('form >  input[type="submit"]').addClass('btn btn-success pull-right');


        $( ".small-img" ).on( "click", function() {
            var source=$( this ).attr('src');
            $(".main-img" ).attr('src',source  );
            $(".main-img" ).parent('a').attr('href',source  );
        });
    });
</script>

