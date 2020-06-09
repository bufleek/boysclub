$(document).ready(function(){
    function hide_overall_display(){
        $("#overall_display").removeClass("unhide_bottom").addClass("hide_bottom");
    }

    function unhide_overall_display(){
        $("#overall_display").removeClass("hide_bottom").addClass("unhide_bottom");
    }

    $("#close_overall_display").click(function(){
        $("#overall_display_content").html("");
        hide_overall_display();
    });

    var members_data = [];

    function get_members(){
        $.post("static/includes/action.php", {get_members: ""}, function(data){
            data = JSON.parse(data);

            members_data = [];
            for(var a = 0; a < data.member.length; a++){
                members_data.push([data.member[a], data.names[a], data.payments[a], data.roles[a]]);
            }

            $("#members_tbody").html("");
            let account_total = 0;
    
            for(var a = 0; a < members_data.length; a++){
                account_total += members_data[a][2];
                var member_tr = `
                    <tr id="${members_data[a][0]}" class="member_tr">
                        <td>${members_data[a][1]}</td>
                        <!--<td>null</td>
                        <td>null</td>-->
                        <td>${members_data[a][2]}</td>
                        <td>${members_data[a][3]}</td>
                    </tr>
                `;
    
                $("#members_tbody").append(member_tr);
                $("#members_total").text(a+1);
                $("#account_total").text(account_total);

                if(a == members_data.length-1){
                    $(".member_tr").click(function(){
                        var member = this.id;
                        
                        for(var a = 0; a<members_data.length; a++){
                            if(members_data[a][0] == member){
                                $("#overall_display_content").html(`
                                    <div style="height: 100%; display: flex; flex-direction: column; align-items: center; position: relative;">
                                        <h4>${members_data[a][1]} - Member</h4>
                                        <div>
                                            <span>Contributions : ${members_data[a][2]}<span>
                                        <div>
                                        <div style="position: absolute; bottom: 0; left: 0; background: rgba(0, 0, 0, 0.5); width: 100%; display: flex; padding:1.5rem 0; border-radius: 10px;">
                                            <!--<div style="flex: 1; text-align: center; cursor: pointer; position: relative;" class="asign_role"><span id="show_roles">Asign Role</span>
                                                <div id="roles" style=" position: absolute; top: 0; background: rgba(0, 0, 0, 0.5); width: 100%; min-height: 100%; transform: translateY(calc(-100% - 1.6rem)); padding: 1rem 0; display: none;">
                                                    <span class="warning" id="asign_role_message"></span>
                                                    <div style="padding: 1rem 0;" class="asign_role_chairman" id="${members_data[a][0]}">Chairman</div>
                                                    <div style="padding: 1rem 0;" class="asign_role_secretary" id="${members_data[a][0]}">Secretary</div>
                                                    <div style="padding: 1rem 0;" class="asign_role_treasurer" id="${members_data[a][0]}">Treasurer</div>
                                                    <div style="padding: 1rem 0;" class="asign_role_member" id="${members_data[a][0]}">Member</div>
                                                    <input type="password" id="asign_role_password" placeholder="Password" style="color: #000; margin: 0 1rem";>
                                                </div>
                                            </div>-->
                                            <div style="flex: 1; text-align: center; cursor: pointer; position: relative;" id="${members_data[a][0]}"><span id="add_contribution">Add Contribution</span>
                                                <div id="contrib_container" style="position: absolute; top: 0; background: rgba(0, 0, 0, 0.5); width: 100%; min-height: 100%; transform: translateY(calc(-100% - 1.6rem)); padding: 2rem 1rem; display: none;">
                                                    <form method="POST" id="new_contribution_form" style="max-width: 300px; margin: 0 auto;">
                                                        <span class="warning" id="contribution_message"></span>
                                                        <input type="text" id="contributing_member" value="${members_data[a][0]}" hidden readonly style="color: #000;">
                                                        <label for="new_contribution">Amount</label>
                                                        <input type="number" name="new_contribution" id="new_contribution" placeholder="Amount" min="0" style="color: #000;" class="u-full-width">
                                                        <label for="contribution_password">Password</label>
                                                        <input type="password" name="password" id="contribution_password" placeholder="password" style="color: #000;" class="u-full-width">
                                                        <input type="submit" value="Add" class="button button-primary u-full-width">
                                                    </form>
                                                </div>
                                            </div>
                                        <div>
                                    </div>
                                `);

                                function hide_unhide_roles(){
                                    $("#roles").slideToggle();
                                }

                                function asign_role(role, member){
                                    function asign_role_message(message){
                                        $("#asign_role_message").text(message);
                                        setTimeout(()=>{
                                            $("#asign_role_message").text("");
                                        }, 2000);
                                    }

                                    let asign_role_password = "";
                                    if($("#asign_role_password").val().replace(/ /g, "") == ""){
                                        $("#asign_role_password").val("").addClass("faulty_input").on("animationend", function(){
                                            $("#asign_role_password").removeClass("faulty_input");
                                        });
                                    }
                                    else{
                                        asign_role_password = $("#asign_role_password").val();
                                        $.post("static/includes/action.php", {asign_role: role, member: member, password: asign_role_password}, function(response){
                                            asign_role_message(response);
                                            if(response == "Role Asigned"){
                                                setTimeout(()=>{
                                                    hide_unhide_roles();
                                                },1000);
                                            }
                                            get_members();
                                        });
                                    }
                                }

                                $(".asign_role_chairman").click(function(){
                                    asign_role("chairman", this.id);
                                });

                                $(".asign_role_secretary").click(function(){
                                    asign_role("secretary", this.id);
                                });

                                $(".asign_role_treasurer").click(function(){
                                    asign_role("treasurer", this.id);
                                });

                                $(".asign_role_member").click(function(){
                                    asign_role("member", this.id);
                                });
                                unhide_overall_display();

                                

                                function hide_unhide_contribution(){
                                    $("#contrib_container").slideToggle();
                                }
                                $("#show_roles").click(function(){
                                    hide_unhide_roles();
                                });

                                $("#add_contribution").click(()=>{
                                    hide_unhide_contribution();
                                })

                                $("#new_contribution_form").submit(function(e){
                                    e.preventDefault();
                                    let form_filled = true;
                                    let contribution = $("#new_contribution").val();
                                    let password = $("#contribution_password").val();
                                    let contributing_member = $("#contributing_member").val();
                                    
                                    if(contribution.replace(/ /g, "") == ""){
                                        form_filled = false;
                                        $("#new_contribution").val("").addClass("faulty_input").on("animationend", function(){
                                            $("#new_contribution").removeClass("faulty_input");
                                        });
                                    }

                                    else if(password.replace(/ /g, "") == ""){
                                        form_filled = false;
                                        $("#contribution_password").val("").addClass("faulty_input").on("animationend", function(){
                                            $("#contribution_password").removeClass("faulty_input");
                                        });
                                    }

                                    if(form_filled){
                                        $.post("static/includes/action.php", {amount: contribution, password: password, member: contributing_member}, function(response){
                                            $("#contribution_message").text(response);
                                            if(response == "Contribution Added"){
                                                setTimeout(()=>{
                                                    hide_unhide_contribution();
                                                }, 1000);
                                            }
                                            get_members();
                                        });
                                    }
                                }); 

                                break;
                            }
                        }
                    })
                }
            }
        });
    }

    get_members();

    $("#add_member").click(function(){
        $("#new_member_container").slideToggle();
        document.querySelector("#new_member").scrollIntoView({
            behavior: "smooth"
        });
    });

    function faulty_name(message){
        $("#new_member_message").html(`<span class="danger">${message}</span>`);
        $("#new_member #name").addClass("faulty_input").on("animationend", ()=>{
            $("#new_member #name").removeClass("faulty_input");
        });
        setTimeout(()=>{
            $("#new_member_message").html("");
        }, 3000);
    }

    $("#new_member").submit(function(event){
        event.preventDefault();
        let formData = new FormData(this);

     if($("#new_member #name").val().replace(/ /g, "") == ""){
         faulty_name("");
     }
     
     else{
         $.ajax({
             url: "static/includes/action.php",
             type: "POST",
             data: formData,
             contentType: false,
             processData: false,
             cache: false,
             beforeSend: function(){
                $("#new_member input").prop("disabled", true);
             },
             success: function(response){
                 if(response == "name exists"){
                     faulty_name("Member Already Exists");
                 }

                 if(response == "registered"){
                    $("#new_member_message").html(`<span class="success">Registered Successfully</span>`);
                    $("#new_member #name").val("")
                    setTimeout(()=>{
                        $("#new_member_message").html("");
                    }, 3000);
                 }
                 $("#new_member input").prop("disabled", false);
                 get_members();
             }
         });
     }
        
    });

    $("#logout").click(function(){
        $.post("static/includes/action.php", {logout: ""}, function(){
            window.location.href = "index.php";
        })
    });

    $("#user").click(()=>{
        $("#user_info_container").slideToggle();
    });

});