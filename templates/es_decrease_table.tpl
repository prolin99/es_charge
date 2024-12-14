<{$toolbar}>

<div class="container"  >
    <form id='frm' class="form-inline"   action="decrease_table.php" method='post'>

        <div class="form-group">
       	<label for="item_id">繳費項目：</label>
      </span><{html_options name=item_id options=$data.item_list selected=$data.seletc_item class='form-control' onchange="submit();"}>
        </div>
    <{if ($data.admin) }>
      <!-- 管理者可處理全校學生    -->
        <div class='form-group row col-6'>

        <div class="col-4">
        <label for="admin_class_id">繳費班級：</label>
        </div>
        <div class="col-4">
        <{html_options name='admin_class_id' options=$data.class_list selected=$data.class_id class='col-3 form-control'  onchange="submit();"}>
        </div>

        <span class="alert alert-danger col-sm-4" title='可修改全校資料，無時限。'>管理員權限!!!!</span>


        </div>
    <{/if}>
   </form>
</div>


<{if ($data.class_id )  }>
<{if ($data.seletc_item ) }>
<{if ($data.inTime)}>

    <div class="alert alert-success">
    <h4>減免說明</h4>
    <{$data.ps}>
    </div>
    <{if ($data.spec_list)}>
    <h4>舊記錄中班上有減免身份(提供參考)</h4>
    <{$data.spec_list}>
    <{/if}>
  <!--            printableArea     開始              -->
  <div id='printableArea' class='container'>
  <h4><{$data.class_list_c[$data.class_id]}>  班上減免總表模式(收費人數：<{$data.selected_count}>人，在籍人數：<{$data.students_count}>人)</h4>
     <{* 多餘繳費人記錄 *}>
     <{if ($data.out_student)   }>
     <div class="alert alert-danger">
        <{foreach  key=stud_sn item=s_stud   from= $data.out_student }>
                <p>繳費記錄中： <{$s_stud.student_sn}> 學生: <{$s_stud.rec_name}>已不在最新的學生名冊中！是否已轉出？<a class="btn btn-danger"  href="javascript:if(confirm('確定學生<{$s_stud.rec_name}>已轉出，在這次收費記錄中已無需繳費?'))location='index.php?mode=del&class_id=<{$s_stud.class_id}>&item_id=<{$s_stud.item_id}>&id=<{$s_stud.record_id}>&sn=<{$s_stud.student_sn}>'">刪除</a></p>
         <{/foreach}>
     </div>
     <{/if}>

  <table class="table table-bordered table-hover" >
  <tr>
      <th scope='col'>姓名</th>
      <th scope='col'>主身份別</th>
      <{foreach  key=detail_key item=detail_val   from= $data.detail_list }>
       <{*       如年段的該項目無收費 則不出現                       *}>
       <{if ($data.detail_dollar.pay[$detail_key])<>0 }>
        <th scope='col' ><i class="fa fa-ban"    set="detailid_<{$detail_key}>"  dollar="<{$data.detail_dollar.pay[$detail_key]}>"  title='全班先減免此項' ></i>
        <{$detail_val}>(<{$data.detail_dollar.pay[$detail_key]}>元)
        </th>
       <{/if}>
      <{/foreach}>
       <th scope='col'>備註</th>
    </tr>

  <{foreach  key=key item=stud   from= $data.students }>
    <{if ($data.selected[$stud.stud_id].selected) }>
    <tr >
     <th >
      (<{$stud.class_sit_num}>)<{$stud.name}>
      <{assign var="stud_id" value=$stud.stud_id}>
     </th>
     <td >
           <{html_options name='cause_id'   class='form-control '    options=$decrease_cause   selected=$data.selected[$stud_id].cause_id   id="cause_$stud_id"   onchange="cause_check($(this));"   }>
      </td>

          <!--            繳費金額(有申請減免                   -->
      <{foreach  key=detail_key item=detail_val   from= $data.detail_list }>
        <{assign var="detai_link" value="$detail_key" }>
        <{*       如年段的該項目無收費 則不出現                       *}>
        <{if ($data.detail_dollar.pay[$detail_key])<>0 }>
        <td >
           <div class="row money_div"  id='box_<{$stud.stud_id}>_<{$detail_key}>'  >
              <span class="col-1">
                <span class="fa fa-forward"    set="dollars_<{$stud.stud_id}>_<{$detail_key}>"  dollar="<{$data.detail_dollar.pay[$detail_key]}>"  title='(<{$stud.class_sit_num}>)<{$stud.name}> , <{$detail_val}>(<{$data.detail_dollar.pay[$detail_key]}>元)'  ></span>
              </span>
              <{*   -----  金額  ------------------     *}>
              <span class="col-8">
                <input   class='form-control money detailid_<{$detail_key}>'  name='dollars[<{$stud.stud_id}>_<{$detail_key}>]' type='text' id='dollars_<{$stud.stud_id}>_<{$detail_key}>'  sit_num = '<{$stud.class_sit_num}>'   title='(<{$stud.class_sit_num}>)<{$stud.name}> , <{$detail_val}>(<{$data.detail_dollar.pay[$detail_key]}>元)'  value='<{$data.decase_list[$stud_id].dollar[$detail_key] }>'   onchange="check_input($(this),<{$data.detail_dollar.pay[$detail_key]}>);" />
              </span>

                      <{*   -----  補助  ------------------     *}>
                      <{if !($data.dent_support[$detail_key]) }>
                        <span  class="col-12 user_<{$stud_id}>" id = "need_<{$stud.stud_id}>_<{$detail_key}>"
                          <{if ($data.selected[$stud_id].cause_id ==0) }>
                          style="display:none"
                           <{/if}>
                          >
                        <div class='row'>
                        <span class='col-5'>
                        <input type='checkbox'   id="ineed_<{$stud.stud_id}>_<{$detail_key}>"  value='1'   class='need_chk'
                          <{if ($data.decase_list[$stud_id].cause_chk[$detail_key])}>checked <{/if}>  <{*  有申請補助  *}>
                          title="申請補助">補
                        </span>

                        <span class='col-5'>
                           <{if ($data.decase_list[$stud_id].other[$detail_key] ) }>
                           <span class="fa fa-filter " id="showOther_<{$stud.stud_id}>_<{$detail_key}>"  style="background-color: yellow"     title="第二種身份--<{$data.decase_list[$stud_id].other_cause_str[$detail_key]}> ">2身
                           <{else}>
                            <span class="fa fa-filter" id="showOther_<{$stud.stud_id}>_<{$detail_key}>"  title="設定第二種身份">2身
                           <{/if}>
                         </span>
                         </span>
                       </div>
                      </span>
                      <{/if}>

              </div>
                    <{* 補充說明文字 *}>
                      <{if !($data.dent_support[$detail_key]) }>

                     <div class="row"  id="other_div_<{$stud.stud_id}>_<{$detail_key}>" style="display:none"  >
                         <{html_options name="other_$stud_id.$detail_key"  class="form-control other_sel" options=$decrease_cause id="other-$stud_id-$detai_link"    selected=$data.decase_list[$stud_id].other[$detail_key]   ref="cause_$stud_id"    title="第二種減免身份"    style="background-color:#CCCCCC" }>
                         </div>
                      <{/if}>

          </td>
      <{/if}> <{* 不收費 *}>


      <{/foreach}>
      <td  >
          <input   name='ps[<{$stud.stud_id}>]' type='text' id='ps_<{$stud.stud_id}>'  class='form-control' title='(<{$stud.class_sit_num}>)<{$stud.name}>'  value='<{$data.selected[$stud_id].ps}>'   onchange="ps_save($(this));" />
      </td>

     </tr>
    <{/if}>
  <{/foreach}>
  </table>
  </div>


  <{else}>   <{*   intime *}>
  <div class="alert alert-danger">已過填報時限！</div>
  <{/if}>

<{if ($data.inTime)}>
<script type="text/javascript">


//身份補助選擇
function cause_check(obj_sel) {

       var chk = $(obj_sel).val();
       var st_id = $(obj_sel).attr('id');
       //alert(st_id) ;
       var splits = st_id.split(/[_-]/) ;
       var stud_id = splits[1] ;
       $('#need_' + splits[1] +'_'+ splits[2] ) .hide() ;
       //alert(chk) ;
       if  (chk!=0) {
          $('.user_' + splits[1] ) .show() ;
        }else {
           $('.user_' + splits[1] ) .hide() ;
           //alert('.user_' + splits[1] ) ;
        }
        save_cause( '0' , <{$data.seletc_item }>, <{$data.class_id}> ,splits[1] ,  chk  ) ;
 }

// ps
   function ps_save(obj_input ){
      var ps = $(obj_input).val() ;

       var input_id = $(obj_input).attr("id")  ;
       var splits = input_id.split(/[_-]/) ;
       //alert (ps) ;
       save_cause( '99' , <{$data.seletc_item }>, <{$data.class_id}> ,splits[1] ,  ps  ) ;
    }



     //ajax 存檔後
function save_cause( mode ,  item, class_id , stud_id ,cause  ){
       $.ajax({
              url: 'ajax_decrease_cause.php',
              type: 'POST',
              data: { mode:mode , item_id: item , class_id :class_id  , stud_id : stud_id , cause: cause },

               success: function(data){
                    //  alert(data );
                },

                 error:function(xhr, ajaxOptions, thrownError){
                    alert('error:' + xhr.status);
                    alert(thrownError);
                 }
       })
}

//全班全告
$(document).on("click", ".fa-ban", function(){
    if(confirm('是否要先把全班的這項費用設為減免？')) {
        var input_id = $(this).attr("set")  ;
        var money = $(this).attr("dollar")  ;
        //alert( input_id ) ;

        //找到 detailid_# 的 class 要放入金額
        $('.'+input_id).val(money);
        $('.'+input_id).trigger("change");
    }
});

//指定金額鍵 -------------------------------------------------------------------------------------
  $(document).on("click", ".fa-forward", function(){

    var input_id = $(this).attr("set")  ;
    var money = $(this).attr("dollar")  ;

    //如果有特殊身份
    var splits = input_id.split(/[_-]/) ;
    //alert($('#stu_' + splits[1] ) .val() ) ;
    var stud_id =  splits[1] ;
    var detail_id = splits[2] ;

    var cause_id = $('#cause_' + splits[1] ) .val()  ;
    if ( cause_id  > 0 )    //有特別身份，出現補助等訊息
      $('#need_' + splits[1] +'_'+ splits[2] ) .show() ;
    else
      $('#need_' + splits[1] +'_'+ splits[2] ) .hide() ;

      //有值清空，無值填入
    var old_data= $('#'+input_id).val() ;
    if (old_data) {
      $('#'+input_id).val('') ;
    }else   {
      $('#'+input_id).val(money) ;
    }

    get_decrease_val( input_id ) ;
  });

//取值，寫入資料
function get_decrease_val( input_id ){
    var splits = input_id.split(/[_-]/) ;

    var stud_id =  splits[1] ;
    var detail_id = splits[2] ;

    var cause_id = $('#cause_' + splits[1] ) .val()  ;
    //var need = $('#ineed_' + stud_id +'_'+ detail_id ).attr('checked') ;

    var need =0 ;
    //if ( $('#ineed_' + stud_id +'_'+ detail_id ).attr('checked')==undefined )
    //預設為無補助
    if ( $('#ineed_' + stud_id +'_'+ detail_id ).prop('checked')== true  )
        need =1
    else
       need= 0 ;

    //第二原因
    var other = $('#other-' + stud_id  +'-' + detail_id).val() ;
    if  (other==undefined )
        other= 0 ;

    var money = $('#dollars_'+ stud_id  +'_' + detail_id).val()  ;
    if  (!isInteger(money) )
      money= 0 ;
    var sit_num = $('#dollars_'+ stud_id  +'_' + detail_id).attr('sit_num') ;

    // 修改金額，一定要存。其他觸動如果無金額申請等略去。
    var do_save = false ;
    if  ( (splits[0] == 'dollars' ) ||  (money >0 )  ){
        do_save = true ;
    }

    if  (do_save) {
        //alert(<{$data.seletc_item }>+',' +<{$data.class_id}> +',' +detail_id+',' +stud_id +',' +sit_num + ',' +money+ ',' +cause_id + ',' +need +',' + other  ) ;
        save_decrease( <{$data.seletc_item }>, <{$data.class_id}> , detail_id , stud_id ,  sit_num , money ,  cause_id , need , other  ) ;
    }
}

//減免金額等
 function save_decrease( item_id, class_id , detail_id , stud_id ,  sit_num , money ,  cause_id , need , other  ) {
       $.ajax({
              url: 'ajax_decrease.php',
              type: 'GET',
              data: { item_id: item_id , class_id :class_id  , detail_id:detail_id , stud_id : stud_id , sit_num:sit_num , money:money , cause: cause_id ,need:need , other:other },

               success: function(data){
                //  alert(data);
                },

                 error:function(xhr, ajaxOptions, thrownError){
                    alert('error:' + xhr.status);
                    alert(thrownError);
                 }
       })

 }

//申請鍵
  $(document).on("click", ".need_chk", function(){
        var input_id = $(this).attr("id")  ;
        get_decrease_val(input_id) ;
    })


//出現第二身份 (color change) other- (非底線)
$( ".fa-filter" ).click(function( event ) {
  var input_id = $(this).attr("id")  ;
  var splits = input_id.split(/[_-]/) ;

  var money = $('#dollars_'+ splits[1]  +'_' + splits[2] ).val()  ;
  if  (!isInteger(money) )
      money= 0 ;

  if ( ( $('#ineed_' + splits[1] +'_'+ splits[2] ).prop('checked') )   && (money>0)  ){
      $('#other_div_' +splits[1] + '_' + splits[2]  ).toggle() ;
      $('#other-' +splits[1] + '-' + splits[2]  ).focus() ;
  }

  $(document).on("change", ".other_sel", function(){
        var input_id = $(this).attr("id")  ;

        var other = $(this).val() ;
        //alert(other) ;
        var splits = input_id.split(/[_-]/) ;
        //alert('#showOther_' +splits[1] + '_' + splits[2] + '='  +other) ;

        if (other == 0) {
             $('#showOther_' +splits[1] + '_' + splits[2]  ).removeAttr("style");
             $('#showOther_' +splits[1] + '_' + splits[2]  ).attr( 'style' , "background-color: white"  ) ;

        }else  {
            $('#showOther_' +splits[1] + '_' + splits[2]  ).removeAttr("style");
            $('#showOther_' +splits[1] + '_' + splits[2]  ).attr( 'style' , "background-color: yellow"  ) ;
        }

        get_decrease_val(input_id) ;
  })

});
/*
$( ".money_div" ).mouseleave(function( event ) {
  var input_id = $(this).attr("id")  ;
  var splits = input_id.split(/[_-]/) ;
  $('#other_div_' +splits[1] + '_' + splits[2]  ).toggle() ;
});

  //other_sel
  $(document).on("change", ".other_sel", function(){
        var input_id = $(this).attr("id")  ;
        get_decrease_val(input_id) ;
  })
*/
  function isInteger(value) {
      return (value == parseInt(value));
  }


   function check_input(obj_input , max_m){
      var g_val = $(obj_input).val() ;
      g_val = g_val.trim() ;
      var input_name = $(obj_input).attr('title') ;
       if (g_val ) {
         if (  (g_val > max_m) | (g_val<0)  | (! isInteger(g_val) ) ) {
             alert (  input_name+ '減免金額有問題！') ;
              $(obj_input).val(0) ;
          }
       }
       var input_id = $(obj_input).attr("id")  ;
       get_decrease_val(input_id) ;
    }

</script>



<{/if}>  <{*  data.inTime *}>

  <{/if}>          <{*  data.selected   *}>

<{else}>           <{*  data.class_id  *}>
  <h4>未選擇繳費項目或非級任身份，無法使用！</h4>
 <{/if}>
