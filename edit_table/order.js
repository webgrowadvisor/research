function getJsGridData(university_id) {
    $.ajax({
        type : 'POST',
        dataType: 'json',
        url: BASE_URL + "Actiongetdata/getJsGridData",
        data : {'university_id':university_id, 'table':'university'},
        success : function(data){
            responseData =JSON.parse(JSON.stringify(data));
            console.log(responseData);

            const selectElementsession = document.getElementById('session_id');
            selectElementsession.innerHTML = '';
            Object.keys(responseData['sessionArr']).forEach(function(item){
                var arr = responseData['sessionArr'][item];
                const option = document.createElement('option');
                option.value = arr.session_id; // Assuming your JSON has a 'value' property
                option.text = arr.session_name; // Assuming your JSON has a 'text' property
                selectElementsession.appendChild(option);
            });
            // getSubjects(university_id);

            const selectElementworktype = document.getElementById('worktype');
            selectElementworktype.innerHTML = '';
            Object.keys(responseData['worktypeArr']).forEach(function(item){
                var arr = responseData['worktypeArr'][item];
                const option = document.createElement('option');
                // option.value = arr.worktype_id; // Assuming your JSON has a 'value' property
                option.value = arr.univ_worktype_id; // Assuming your JSON has a 'value' property
                option.text = arr.worktype_name; // Assuming your JSON has a 'text' property
                selectElementworktype.appendChild(option);
            });

             $("#order-subjects").tagit({
                availableTags: responseData['subjectArr'].split(',').map((tag) => tag.trim()).filter((tag) => tag.length !== 0)
            });
        }
    });
}



function ModalData(university_id) {
    $.ajax({
        type : 'POST',
        dataType: 'json',
        url: BASE_URL + "Actiongetdata/getJsGridData",
        data : {'university_id':university_id, 'table':'university'},
        success : function(data){
            responseData =JSON.parse(JSON.stringify(data));
            console.log(responseData);

            const selectElementsession = document.getElementById('modal-session_id');
            selectElementsession.innerHTML = '';
            Object.keys(responseData['sessionArr']).forEach(function(item){
                var arr = responseData['sessionArr'][item];
                const option = document.createElement('option');
                option.value = arr.session_id; // Assuming your JSON has a 'value' property
                option.text = arr.session_name; // Assuming your JSON has a 'text' property
                selectElementsession.appendChild(option);
            });
            // getSubjects(university_id);

            const selectElementworktype = document.getElementById('modal-worktype');
            selectElementworktype.innerHTML = '';

            Object.keys(responseData['worktypeArr']).forEach(function(item){
                var arr = responseData['worktypeArr'][item];
                const option = document.createElement('option');
                option.value = arr.univ_worktype_id; // Assuming your JSON has a 'value' property
                option.text = arr.worktype_name; // Assuming your JSON has a 'text' property
                selectElementworktype.appendChild(option);
            });

            
            

            // 1. Split the string into an array
            var subjects = responseData['subjectArr'].split(',').map(s => s.trim()).filter(Boolean);
            // console.log("Subjects:"+subjects);

            
            var $select = $("#modal-order-subjects");   // 2. Get the <select> element
            $select.empty();                            // 3. Clear any existing options

            // 4. Add options (not selected)
            subjects.forEach(subject => {
                var option = new Option(subject, subject, false, false);
                $select.append(option);
                // $select.trigger("change");
            });

            // 5. Initialize Select2 (or re-initialize)
            $select.select2({
                placeholder: "Select subjects",
                width: "resolve"
            });

        }
    });
}

function updatejsGridData(control,value,where) {
    // filed_name, give_val, order_id
    // alert(control+"##"+value+"$$"+where)

    $.ajax({
    type: "POST",
    url:  BASE_URL +"actionupdate/ordersJSGrid", 
    dataType: 'json',
    data:{"update": control, "where":where,"value":value}
    }).done(function(response) {
        if(response.error)
            $.gritter.add({title:response.title,text:response.message,image:BASE_URL+"assets/img/error.png"});
        else
        {
            $.gritter.add({title:response.title,text:response.message,image:BASE_URL+"assets/img/success.png"});
            // setTimeout(function(){location.reload();}, 3000);
            $("#jsGrid").jsGrid("loadData");
        }
    });
}

function getSubjectsjsGrid(params) {
    alert(params);
    $.ajax({
        type : 'POST',
        dataType: 'json',
        url: BASE_URL + "Actiongetdata/getSubjectsjsGrid",
        data : {'university_id':university_id, 'table':'university'},
        success : function(data){
            responseData =JSON.parse(JSON.stringify(data));
        }
    });
}


// file upload function
function update_data_order(formid, url) {
    var formElement = document.getElementById(formid);
    var data = new FormData(formElement);
    $.ajax({
        url: url,
        type: "POST",
        data: data,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
          console.log(response);
            if (response.error) {
                $.gritter.add({title: response.title, text: response.message, image: BASE_URL+"assets/img/error.png"});
            } else {
                $.gritter.add({title: response.title, text: response.message, image: BASE_URL+"assets/img/success.png"});

                $('#modal-start').modal('hide');
            }
        }
    });
}
// end file update
