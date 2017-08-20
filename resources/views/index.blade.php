@extends('layouts.app')

@section("title")
    หน้าแรก
@endsection

@section("content")
<div class="section no-pad-bot" id="index-banner">
    <div class="container">
        <br><br>
        <img class="center" style="max-width: 960px; width: 100%; display: block; margin: 0 auto;" src="/img/logo.png"/>
        <div class="row center">
            <h5 class="header col s12 light black-text" style="line-height: 45px">หากคุณกำลังต้องการจะหาสาระ คุณได้มาผิดที่แล้ว...</h5>
        </div>
    </div>
</div>
<div class="container">
    <div class="section">

        <div class="z-depth-1 card-panel white" style="max-width:800px;margin: 3rem auto auto;">
            <h5 class="center">ประวัติความเป็นมา</h5>

            <p>Buffalolarity คือนามของราชาแห่งทวยเทพ ผู้อยู่ ณ จุดสูงสุดของทุกสิ่งทุกอย่าง ท่านเป็นลูกหลานขององค์บัฟฟี่และองค์ลาริตี้ผู้มีรูปร่างเป็นหมาและควาย พวกเราในฐานะมนุษย์ที่ถูกส่งลงมาบนโลกใบนี้ต้องถูกปกครองให้อยู่ในโอวาทของท่านบัฟ ท่านจึงได้ส่งเอกอัครราชทูตมาประจำยังโลกใบนี้ นามของเขาคือ "พลเอก ศาสตราจารย์ ดร. หม่อมราชวงศ์ แจ๊ค เดอ บัฟฟาโล่ลาริตี้ ดาร์คเฟรมมาสเตอร์ เกลซอนอันซัส ดิ เอกซ์ คาลิเบอร์"</p>
        </div>

    </div>
    <br><br>
</div>
@endsection
