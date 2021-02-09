<style>
.btn1 {
  width: 100%;
  padding: 12px;
  border: none;
  border-radius: 4px;
  opacity: 0.85;
  display: inline-block;
  font-size: 17px;
  line-height: 20px;
  text-decoration: wavy; /* remove underline from anchors */
}
.btn1:hover {
  opacity: 1;
}
.google {
  background-color: #dd4b39;
  color: white;
}
h4 {
   width: 100%; 
   text-align: center; 
   border-bottom: 1px solid #000; 
   line-height: 0.1em;
   margin: 10px 0 20px; 
} 

h4 span { 
    background:#fff; 
    padding:0 10px; 
}
</style>
<center><h3>Login to start your session</h3></center><br />
<?php echo '<div align="center">'.$login_button . '</div>'; ?> 
<div><h4><span>OR</span></h4></div>
<form id="login_form" method="POST" action=""> 
    <div id="error-box" class="callout callout-danger" style="display:none;"> 
        <h4><i class="icon fa fa-warning"></i> Error!</h4>
        <span id="login_err"></span>
    </div>
    <div class="form-group input-group">
        <input type="text" name="username" class="form-control" placeholder="Username" />
          <span class="input-group-btn">
            <button class="btn btn-default" type="button"><i class="glyphicon glyphicon-user"></i></button>
          </span>
    </div>
    <div class="form-group input-group">
        <input type="password" name="password" class="form-control pwd" placeholder="Password" value="">
          <span class="input-group-btn">
            <button class="btn btn-default reveal" type="button"><i class="glyphicon glyphicon-eye-open"></i></button>
          </span>
    </div>
    <div class="row">
        <div class="col-xs-8">
            
        </div><!-- /.col -->
        <div class="col-xs-4">
            <button type="button" id="login_submit_btn" onclick="javascript: submitdata_auth('login_form','auth/validate');return false;" class="btn btn-primary btn-block btn-flat">Login</button>
        </div><!-- /.col -->
    </div>
</form>
<div style="clear:both;height:20px;"></div>
<div class="row">
    <div class="col-md-12"><b>Version</b> 1.2</div>
</div>
<strong>Powered by <a href="http://stech.ai/" target="_blank">STech.ai</a>.</strong><br>
<strong>Copyright &copy; 2020</strong>. All rights reserved.


