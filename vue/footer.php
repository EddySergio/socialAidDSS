<script>
      let sidebar = document.querySelector(".sidebar");
      let sidebarBtn = document.querySelector(".sidebarBtn");
      sidebarBtn.onclick = function () {
        sidebar.classList.toggle("active");
        if (sidebar.classList.contains("active")) {
          sidebarBtn.classList.replace("bx-menu", "bx-menu-alt-right");
        } else sidebarBtn.classList.replace("bx-menu-alt-right", "bx-menu");
      };
</script>
    <script type="text/javascript" src="../public/js/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" src="../public/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="../public/js/dataTables.bootstrap4.min.js"></script>
    <script type="text/javascript" src="../public/js/select2.min.js"></script>

  </body>
</html>
