<style>
.bgsuccess {
    background-color: #198754;
}
.bgwarning {
    background-color: #FFC107;
}
.bginfo {
    background-color: #0DCAF0;
}

</style>
<{$toolbar}>

<div class="container"  >
    <form id='frm' class="form-inline"   action="decrease.php" method='post'>
      <div class="form-group">
      <label for="item_id">繳費項目：</label>
      <{html_options name=item_id options=$data.item_list selected=$data.seletc_item class='form-control' onchange="submit();"}>
    </div>
   </form>
</div>
<{if ($data.class_id )  }>
<{*     減免列表            *}>
<{if ($data.seletc_item ) }>


<h3><{$data.class_list_c[$data.class_id]}>  減免學生金額列表(收費人數：<{$data.selected_count}>人，在籍人數：<{$data.students_count}>人)</h3>

        <table class="table table-bordered table-hover" >
	<!--      表格標題                                   -->
    <tr>

      <th scope='col'>座號</th>
      <th scope='col'>姓名</th>

      <{foreach  key=detail_key item=detail_val   from= $data.detail_list }>
        <th scope='col'><{$detail_val|truncate:9}><span class="label"><{$data.detail_dollar.pay[$detail_key]}></span></th>
      <{/foreach}>

      <th scope='col'>減免原因</th>

  </tr>
      <!--      表格內容                                   -->

    <{foreach  key=key item=stud   from= $data.decase_list }>

    <tr>
      <div  id="div_<{$data.seletc_item}>_<{$key}>">
      <th scope='col'><div    id="sit_<{$data.seletc_item}>_<{$key}>" data='<{$data.students[$key].class_sit_num}>'  ><{$data.students[$key].class_sit_num}></div></td>
      <td  id="name_<{$data.seletc_item}>_<{$key}>" data='<{$data.students[$key].name}>' ><{$data.students[$key].name}></td>

      <{foreach  key=detail_key item=detail_val   from= $data.detail_list }>
        <{*    dollar_item_stud_sn_detail_id                金額  checked 補助                   *}>
        <{if ($data.decase_list[$key].cause_chk[$detail_key])}>
          <{*  cause_chk   *}>
          <{if ($data.decase_list[$key].other[$detail_key])}>
             <{*   other cause_chk   *}>
            <td   id='dollar_<{$data.seletc_item}>_<{$key}>_<{$detail_key}>' data='<{$data.decase_list[$key].dollar[$detail_key]}>' need='checked'  ><span class="bgwarning badge badge-warning bg-warning" title='申請補助,其它身份別(<{$data.decase_list[$key].other_cause_str[$detail_key]}>)'><{$data.decase_list[$key].dollar[$detail_key]}></span></td>
          <{else}>
            <td   id='dollar_<{$data.seletc_item}>_<{$key}>_<{$detail_key}>' data='<{$data.decase_list[$key].dollar[$detail_key]}>' need='checked'  ><span class="bginfo badge badge-info bg-info" title='申請補助'><{$data.decase_list[$key].dollar[$detail_key]}></span></td>
          <{/if}>

        <{else}>
        <td   id='dollar_<{$data.seletc_item}>_<{$key}>_<{$detail_key}>' data='<{$data.decase_list[$key].dollar[$detail_key]}>' need=''  ><{$data.decase_list[$key].dollar[$detail_key]}></td>
        <{/if}>
      <{/foreach}>

      <td  id='cause_<{$data.seletc_item}>_<{$key}>' data='<{$data.selected[$key].cause|truncate:18}>'><span class="del"><i class="fa fa-trash" title='刪除減免記錄'></i></span><{$data.selected[$key].cause|truncate:18}>
          <{if ($data.selected[$key].ps) }>
            <span class="fa fa-info-circle" title="連絡內容：<{$data.selected[$key].ps}>"></span>
          <{/if}>
        </td>

      </div>
  </tr>
    <{/foreach }>
</table>

	 <{if ($data.inTime ) }>

     <{* 多餘繳費人記錄 *}>
     <{if ($data.out_student)   }>
     <div class="alert alert-danger">
        <{foreach  key=stud_sn item=s_stud   from= $data.out_student }>
                <p>繳費記錄中： <{$s_stud.student_sn}> 學生: <{$s_stud.rec_name}>已不在最新的學生名冊中！是否已轉出？<a class="btn btn-danger"  href="javascript:if(confirm('確定學生<{$s_stud.rec_name}>已轉出，在這次收費記錄中已無需繳費?'))location='index.php?mode=del&class_id=<{$s_stud.class_id}>&item_id=<{$s_stud.item_id}>&id=<{$s_stud.record_id}>&sn=<{$s_stud.student_sn}>'">刪除</a></p>
         <{/foreach}>
     </div>
     <{/if}>

	 <hr />
       <{* 新增學生        --------------------------------------------------------------------------------------                            *}>
     <div  id= 'frm_add' >
         <div class="col-8">

       <form method='post' name='editForm' id='editForm_new' class="form-horizontal" >
 		<fieldset>

    <{if ($data.admin) }>
    	<!-- 管理者可處理全校學生    -->
    	 <div class="form-group row col-mb-3">
       <label class="col-sm-3 control-label col-form-label text-md-right text-md-end">
       繳費班級：
       </label>
       <div class="col-sm-4">
       <{html_options class='form-control' name='admin_class_id' options=$data.class_list selected=$data.class_id   onchange="submit();"}>
       </div>
       <div class="col-sm-4">
       <span class="alert alert-danger col-sm-2" title='可修改全校資料，無時限。'>管理員權限!!!!</span>
       </div>
    	</div>

	<{else}>
    	<legend>新增減免學生</legend>
    <{/if}>
      <table class="table table-bordered table-hover " >
        <tr>
     		<td >
            學生：
            <select name="stud" class="form-control" >
			<option label="選擇學生" value="0" >選擇學生</option>
			 <{foreach  key=key item=stud   from= $data.students }>
			  	<{if ($data.selected[$stud.stud_id].selected and  !$data.decase_list[$stud.stud_id]) }>
				<option label="(<{$stud.class_sit_num}>)<{$stud.name}>" value="<{$stud.stud_id}>_<{$stud.class_sit_num}>"  >(<{$stud.class_sit_num}>)<{$stud.name}></option>
    			<{/if}>
     		 <{/foreach }>
			</select>
			</td>
        </tr>

		<tr>
		<td>主減免原因：
     			<{html_options name='cause_id'  class="form-control" options=$decrease_cause  onchange="cause_check($(this));"     }><br/>
                        (少數有多身份,點補助後圖示指定)
        </td>
        </tr>


 		<{*  各項費用  ---------------------   *}>
 		<{foreach  key=key item=detail   from= $data.detail_list }>
      <{*       如年段的該項目無收費 則不出現                       *}>
      <{if ($data.detail_dollar.pay[$key])<>0 }>
 		<tr>
        <td>
            <div >
     			<span class="fill  col-8" id= "fill_dollars<{$key}>_<{$data.detail_dollar.pay[$key]}>"   title="點選填入金額或清空">
     			<span   for="dollars[<{$key}>]" ><{$detail}>(<{$data.detail_dollar.pay[$key]}>元):</span>
                <i class="fa fa-forward"></i>
                </span>

      			<input    name='dollars[<{$key}>]' type='text' id='dollars<{$key}>'  title='<{$detail}><{$data.detail_dollar.pay[$key]}>'  tabindex='<{$key}>'
      			    placeholder='<{$data.detail_dollar.decease[$key]}>' class="form-control col-4"  onchange="check_input($(this),<{$data.detail_dollar.pay[$key]}>);" />
            </div>

            <div class="row">
  			    <div class="input-append col-3">
                      <{if ($data.dent_support[$key]) }>
                      <label title='可減免，但無法補助' class="label">(無補助)</label>
                      <{else}>
                        <span  >
 			                  <label title='不參加的情況，請不要勾選補助'><input type='checkbox' name='decrease_sel[<{$key}>]' value='1'  >申請補助</label>
                        </span>
                      <{/if}>

 			     </div>
                      <div class="input-append col-1">
                      <{if !($data.dent_support[$key]) }>

                        <span class="fa fa-filter show_other" title='其他身份別選擇' data_ref='other_<{$key}>'  ></span>

                      <{/if}>
                      </div>
                    <div class="input-append col-3">
                     <{html_options name='other[$key]'   class="form-control" options='$decrease_cause' id='other_$key'   title="其他減免身份"  style="background-color: #CCCCCC;display:none"  }>
                    </div>
            </div>
 		</td>
       </tr>
      <{/if}> <{* end      如年段的該項目無收費 則不出現                       *}>
 			<{/foreach }>

                      <{*    聯絡註記   *}>
 			<tr>
                <td>
                    <span  for="cause_id"  >聯絡註記：</span>
                    <input   class='form-control'  name='ps' type='text'  title='其他補充說明文字(70字內)'  placeholder='其他補充說明文字(70字內)'  />
                    </div>
                </td>
            </tr>
            <tr>
                <td>
			<div class="form-actions">
			<input name='item_id' type='hidden' value='<{$data.seletc_item}>' />
			<input name='class_id' type='hidden' value='<{$data.class_id}>' />

 			<button class="btn btn-primary" type="submit" name='act_add' value='act_add' >新增</button>

 			</div>
        </td>
    </tr>
</table>
 		</fieldset>
		</form>
    </table>
		<{if ($data.spec_list)}>
		<h4>舊記錄中班上有減免身份</h4>
		<{$data.spec_list}>
		<{/if}>
	</div>
	<div class="col-4">
		<p>&nbsp</p><p>&nbsp</p>
	 	<div class="alert alert-success">
		<h4>減免說明</h4>
	 	<{$data.ps}>
	 	</div>
	</div>
	</div>
	<{else}>
	<div class="alert alert-danger">已過填報時限！</div>
	<{/if}>

<{/if}>

 <{if ($data.inTime ) }>
<script>



    //刪除----------------------------------------------------------------------------------
  $(document).on("click", ".del", function(){

    var div_id = $(this).parent().attr("id")  ;
    	   if(confirm('是否確定要刪除？')) {
              // alert(div_id ) ;
              ajax_del(  div_id) ;  // 刪除動作
              //把這個 div 隱藏起來
              $(this).parent().parent().hide() ;
           }
 });

      var ajax_del=function( id ){
      //alert(id) ;
             var URLs="ajax_decrease_del.php?id=" + id ;

            $.ajax({
                url: URLs,
                type:"GET",
                dataType:'text',

                success: function(msg){
                    //alert(msg);
                },

                 error:function(xhr, ajaxOptions, thrownError){
                    alert('error:' + xhr.status);
                    alert(thrownError);
                 }
           })
        }

//----------------------------------------------------------------------
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
    }



    function cause_check(obj_sel) {
	//身份補助選擇

             var chk = $(obj_sel).val();
 		//alert(chk) ;
              //有特殊身份，預設補助勾選
             if  (chk!=0) {
			$('label').find(':checkbox').prop('checked', true );
		}else
			$('label').find(':checkbox').prop('checked' ,false);

 	}

    //修改----------------------------------------------------------------------------------
  $(document).on("click", ".edit", function(){

    //var div_id = $(this).parent().attr("id")  ;
    //alert (div_id) ;

    var splits = div_id.split('_') ;
    var form_id =  'form_' +splits[1]+'_'+ splits[2]   ;
    //取座號、姓名
    var name_str = $('#name_'+splits[1]+'_'+ splits[2]).attr("data")  ;
     var sit_str = $('#sit_'+splits[1]+'_'+ splits[2]).attr("data")  ;
     var cause_str = $('#cause_'+splits[1]+'_'+ splits[2]).attr("data")  ;
   //各項費用取舊值
   <{foreach  key=detail_key item=detail_val   from= $data.detail_list }>
    var dollar_<{$detail_key}> = $('#dollar_'+splits[1]+'_'+ splits[2] +'_<{$detail_key}>').attr("data")  ;
    var check_<{$detail_key}> = $('#dollar_'+splits[1]+'_'+ splits[2] +'_<{$detail_key}>').attr("need")  ;

    <{/foreach}>

        //各項費用輸入
        var form_str =
        		"<form  id='" + form_id + "'  class='form-actions'>"+
        		"<div class='col-1'>"+sit_str+"</div>"+
      			"<div class='col-1'>"+name_str+"</div>" +

      			<{foreach  key=detail_key item=detail_val   from= $data.detail_list }>
        		"<div class='col-1'> "+
                       "<input class='col-10' name='dollars[<{$detail_key}>]' type='text' id='dollars[<{$detail_key}>]'  title='<{$detail_val}>'  tabindex='9<{$detail_key}>' value='"+ dollar_<{$detail_key}> +"'  onblur='check_input($(this),<{$data.detail_dollar.pay[$detail_key]}>);' />" +
                        <{if !($data.dent_support[$detail_key])}> <{*  可否請補助   *}>
  			   " <label ><input type='checkbox' name='decrease_sel[<{$detail_key}>]' value='1' "+ check_<{$detail_key}> +" title='需有身份別才可勾選！'>補助</label>" +
                        <{/if}>
 			" </div>" +
 			<{/foreach}>
 			"<div class='col-2'>" +
 			cause_str +
 			"</div>" +
 			"<span class='save'>save</span>"+
 			"<input name='class_id' type='hidden' value='<{$data.class_id}>' />" +
 			"<input name='item_id' type='hidden' value='<{$data.seletc_item}>' />" +
 			"<input name='name' type='hidden' value='" + name_str+ "' />" +
 			"<input name='sit_str' type='hidden' value='" + sit_str+ "' />" +
 			"<input name='stud' type='hidden' value='" +splits[2] +"' />" +
 			"<input name='cause_str' type='hidden' value='" +cause_str +"' />" +
 			"</form>";

       //編修
	$('#' +div_id).html(form_str) ;
	//新增區hide
	$('#frm_add').hide() ;
 });

   //按下 save  ----------------------------------------------------------------------------------
   $(document).on('click', '.save', function(){
    	var div_id = $(this).parent().parent().attr("id")  ;
    	var form_id = $(this).parent().attr("id")  ;
    	//alert(div_id +form_id ) ;
    	ajax_save( div_id , form_id ) ;
    	$('#frm_add').show() ;
     });

     //ajax 存檔後，修改 div 內容 ----------------------------
      function ajax_save( div_id , form_id  ){
 	//alert ($('#'+form_id ).serialize()) ;
            $.ajax({
              	url: 'ajax_decrease_save.php',
              	 type: 'POST',
		dataType: 'html',
		data: $('#'+form_id ).serialize() ,

                success: function(data){
                    //alert(msg);
                    $('#'+div_id).html(data );
                },

                 error:function(xhr, ajaxOptions, thrownError){
                    alert('error:' + xhr.status);
                    alert(thrownError);
                 }
           })
        }





   //按下 >>   填入全額 -------------------------------------------------
   $(document).on('click', '.fill', function(){

    	var div_id = $(this).attr("id")  ;


    	var splits = div_id.split('_') ;
    	//alert(splits[1]) ;
    	if ($('#'+splits[1]).val() >0  )
    		$('#'+splits[1]).val('') ;
    	else
    	 	$('#'+splits[1]).val( splits[2]) ;

     });

  // other_show
  $(document).on("click", ".show_other", function(){
      var div_id = $(this).attr("data_ref")  ;
      $('#'+div_id).show() ;
    }) ;

</script>
<{/if}>

<{else}>
  <h4>未選擇繳費項目或非級任身份，無法使用！</h4>
 <{/if}>
