<section class="title">
   <ul>
      <li style="float:left;">
        <h4><?php echo lang('pf_entry_list_title') ?></h4>
      </li>
    </ul>
</section>

<section class="item">
    <div class="content">
    <?php if (!empty($entries)): ?>
        <?php echo form_open('admin/pyroforms/delete_logs','class="crud"') ?>
        <input type="hidden" name="btnAction" value="delete" />

        <table cellspacing="0" border="0" class="table-list" id="frm-<?php echo $form_id; ?>">
            <thead>
                <tr>
                    <th><?php echo form_checkbox(array('name' => 'action_to_all', 'class' => 'check-all'));?></th>
                    <th><?php echo lang('pf_username_label'); ?></th>
                    <th><?php echo lang('pf_created_label'); ?></th>
                    <th>IP</th>
                    <th><?php echo lang('pf_client_label'); ?></th>
                    <th>OS</th>

                    <?php foreach ($fields as $id => $label): ?>
                    <th id="header-<?php echo html_escape($id); ?>"><?php echo html_escape($label); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($entries as $entry):?>
            <?php !empty($entry->data) and $entry->data = @unserialize($entry->data, array('allowed_classes' => false))?>
                <tr id="entry-<?php echo (int) $entry->id ?>">
                <td class="action-to"><?php echo form_checkbox('action_to[]', $entry->id) ?></td>
                <td><?php echo html_escape((!empty($entry->username)) ? $entry->username : 'Guest'); ?></td>
                <td><?php echo format_date($entry->created_on); ?></td>
                <td><?php echo html_escape($entry->ip); ?></td>
                <td><?php echo html_escape($entry->uagent); ?></td>
                <td><?php echo html_escape($entry->os); ?></td>
                <?php foreach ($fields as $id => $label): ?>

                <?php $val = isset($entry->data[$id]) ? $entry->data[$id] : ''; ?>

                    <td><?php
                        if (is_array($val))
                        {
                            echo html_escape(implode(', ', $val));
                        } else{
                            if ($types[$id] == 'file')
                            {
                                echo html_escape($val);
                            }else echo (empty($val)) ? '&nbsp;' : html_escape($val);
                        }
                        ?>
                    </td>
                <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot></tfoot>
        </table>
        <br />
        <div class="buttons">
        <button type="submit" name="btnAction" value="<?php echo lang('pf_global:delete'); ?>" class="btn red">
            <span><?php echo lang('pf_global:delete'); ?></span>
        </button>
    </div>
        <?php //$this->load->view('admin/partials/buttons', array('buttons' => array('delete'))); ?>

            <?php echo form_close(); ?>
    <?php else: ?>
        <div class="no_data">
    <?php echo lang('pf_no_forms'); ?>
        </div>
<?php endif; ?>
</div>
</section>
<script>


            /* API method to get paging information */
            $.fn.dataTableExt.oApi.fnPagingInfo = function ( oSettings )
            {
                return {
                    "iStart":         oSettings._iDisplayStart,
                    "iEnd":           oSettings.fnDisplayEnd(),
                    "iLength":        oSettings._iDisplayLength,
                    "iTotal":         oSettings.fnRecordsTotal(),
                    "iFilteredTotal": oSettings.fnRecordsDisplay(),
                    "iPage":          Math.ceil( oSettings._iDisplayStart / oSettings._iDisplayLength ),
                    "iTotalPages":    Math.ceil( oSettings.fnRecordsDisplay() / oSettings._iDisplayLength )
                };
            }

            /* Bootstrap style pagination control */
            $.extend( $.fn.dataTableExt.oPagination, {
                "bootstrap": {
                    "fnInit": function( oSettings, nPaging, fnDraw ) {
                        var oLang = oSettings.oLanguage.oPaginate;
                        var fnClickHandler = function ( e ) {
                            e.preventDefault();
                            if ( oSettings.oApi._fnPageChange(oSettings, e.data.action) ) {
                                fnDraw( oSettings );
                            }
                        };

                        $(nPaging).addClass('pagination').append(
                            '<ul>'+
                                '<li class="prev disabled"><a href="#">&larr; '+oLang.sPrevious+'</a></li>'+
                                '<li class="next disabled"><a href="#">'+oLang.sNext+' &rarr; </a></li>'+
                            '</ul>'
                        );
                        var els = $('a', nPaging);
                        $(els[0]).bind( 'click.DT', { action: "previous" }, fnClickHandler );
                        $(els[1]).bind( 'click.DT', { action: "next" }, fnClickHandler );
                    },

                    "fnUpdate": function ( oSettings, fnDraw ) {
                        var iListLength = 5;
                        var oPaging = oSettings.oInstance.fnPagingInfo();
                        var an = oSettings.aanFeatures.p;
                        var i, j, sClass, iStart, iEnd, iHalf=Math.floor(iListLength/2);

                        if ( oPaging.iTotalPages < iListLength) {
                            iStart = 1;
                            iEnd = oPaging.iTotalPages;
                        }
                        else if ( oPaging.iPage <= iHalf ) {
                            iStart = 1;
                            iEnd = iListLength;
                        } else if ( oPaging.iPage >= (oPaging.iTotalPages-iHalf) ) {
                            iStart = oPaging.iTotalPages - iListLength + 1;
                            iEnd = oPaging.iTotalPages;
                        } else {
                            iStart = oPaging.iPage - iHalf + 1;
                            iEnd = iStart + iListLength - 1;
                        }

                        for ( i=0, iLen=an.length ; i<iLen ; i++ ) {
                            // Remove the middle elements
                            $('li:gt(0)', an[i]).filter(':not(:last)').remove();

                            // Add the new list items and their event handlers
                            for ( j=iStart ; j<=iEnd ; j++ ) {
                                sClass = (j==oPaging.iPage+1) ? 'class="active"' : '';
                                $('<li '+sClass+'><a href="#">'+j+'</a></li>')
                                    .insertBefore( $('li:last', an[i])[0] )
                                    .bind('click', function (e) {
                                        e.preventDefault();
                                        oSettings._iDisplayStart = (parseInt($('a', this).text(),10)-1) * oPaging.iLength;
                                        fnDraw( oSettings );
                                    } );
                            }

                            // Add / remove disabled classes from the static elements
                            if ( oPaging.iPage === 0 ) {
                                $('li:first', an[i]).addClass('disabled');
                            } else {
                                $('li:first', an[i]).removeClass('disabled');
                            }

                            if ( oPaging.iPage === oPaging.iTotalPages-1 || oPaging.iTotalPages === 0 ) {
                                $('li:last', an[i]).addClass('disabled');
                            } else {
                                $('li:last', an[i]).removeClass('disabled');
                            }
                        }
                    }
                }
            } );


/* Set the defaults for DataTables initialisation */
$.extend( true, $.fn.dataTable.defaults, {
    "sDom": "<'row-fluid'<'span6'l><'span6'f>r>t<'row-fluid'<'span6'i><'span6'p>>",
    "sPaginationType": "bootstrap",
    "oLanguage": {
        "sLengthMenu": "_MENU_ per page"
    }
} );

$.datepicker.regional[""].dateFormat = 'dd/mm/yy';
$.datepicker.setDefaults($.datepicker.regional['']);

$(document).ready( function () {
    $('.table-list').dataTable({

        "sDom": "T<'clear'>RC<'clear'>lfrtip",

        "oColVis": {
            "aiExclude": [0],
            "buttonText": "Change columns"
        },
        "bStateSave": true,
        "aoColumnDefs": [{"bSortable": false, "aTargets": [0]}],
        "sPaginationType": "bootstrap",
        "oTableTools": {
            "sSwfPath": "<?php echo $this->module_details['path']?>/js/swf/copy_csv_xls_pdf.swf",
            "aButtons": [
                "copy",
                "print",
                {
                    "sExtends":    "collection",
                    "sButtonText": 'Save <i class="icon-chevron-down" />',
                    "aButtons":    [ "csv", "xls", "pdf" ]
                }
            ]
        }
    });
    /*var o = $('.DTTT_container'),
        w = $('<li style="float:right;margin-right:16px;" />'),
        m = o.clone(true).appendTo(w);
    $('section.title ul').append(w);
    o.remove();*/
});
</script>