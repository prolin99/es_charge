<{$toolbar}>
<div class='container'>

<div id='frm' class="row"  >
    <div class="alert alert-info">
       <h4>說明：</h4>4
        配合 kw_club 社團報名表，轉換成外部扣款 excel  檔。<br>
        1.先由社團報名系統中--報名狀況，匯出Excel 。<br>
        2.整理 EXCEL 中各社團確定名單。<br>
        3.匯入到本系統中，會得到一份外部扣款 EXCEL 檔案。<br>
        4.外部扣款 EXCEL 檔 ，如果出現 *** 年班為 0 ，表示無法在此取得郵局扣款帳號（系統中無幼兒園資料），請再自行補入正確的年、班、座號、帳號等。<br>
        5.修正後的扣款 EXCEL 檔，再交給出納做後續扣款作業。<br>

    </div>

    <form class="form-horizontal"   action="kw_club_join.php" method='post' enctype='multipart/form-data'  >
        <div  class="row"  >
        <h1>kw_club 社團報名轉成外部扣款檔</h1>

        </div>
        <p>
            <span>上傳社團報名 excel 檔：</span>
        <input type=file name=userdata accept='.xlsx'>
        <button type='submit'  name='do_key' value='add_other' class='btn btn-success'  >匯入，取得新扣款檔</button>
         </p>


   </form>

</div>


</div>
