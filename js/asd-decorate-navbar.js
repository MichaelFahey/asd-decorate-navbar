function decorate_navbar_name ( navbar_name_str) {
   navbar_name_html = '<div class="navbar-decoration-name">'.concat( navbar_name_str ).concat( '</div>' );
   jQuery(".navbar-header").prepend( navbar_name_html );
}

function decorate_navbar_icon ( navbar_icon_url, link_url) {
   navbar_icon_html = '<img class="navbar-decoration-logo" src="'.concat( navbar_icon_url ).concat( '" />' );
   navbar_link_html = '<a href="'.concat( link_url ).concat( '">' );
   jQuery(".navbar-header").prepend( navbar_link_html.concat( navbar_icon_html).concat( '</a>' ) );
}

