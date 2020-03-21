 
//creates question

//creates exam from selected questions 
function createExam(event){
    event.preventDefault();
    const examForm = document.querySelector('#eForm');
    const jsonData = {
       name: examForm.elements['examName'].value,
       creator: "snape",
       questions:getCheckedRows('qTable')
    }
    submitJsonData(
        "https://web.njit.edu/~asc8/cs490/beta/middle/exam.php",
        "POST",
        JSON.stringify(jsonData));
    renderExams();
}

//get checked rows 
function getCheckedRows(table){
    let checkedRows = [];
    var table = document.getElementById(table);
    // iterate through rows 
    for (var i = 0, row; row = table.rows[i]; i++) {
        // test if a row is checked 
        if(row.getElementsByTagName('input')[0].checked){
            obj={};
            obj.name = row.cells[1].innerHTML;
            checkedRows.push(obj); 
        }
    }    
    return checkedRows;

}



function assignExam(event){
    event.preventDefault(); 
    const assignForm = document.querySelector('#assignForm');
    let formVal = event.explicitOriginalTarget.value;
    let jsonData = {}
    if(formVal === 'Assign'){
        console.log("assign to students");
        let exams = getCheckedRows('aTable');
        let students = getCheckedRows('sTable');
        students.map((student)=>{
           exams.map((exam) =>{
              let jsonBody = {
                  user: student, 
		  exam: exam
              }
              // fix to use middle endpoint  
              submitJsonData(
                  "https://web.njit.edu/~tg253/490/examservice.php",
                  "POST",
                  JSON.stringify(jsonData));
            
        })});
    }
    if(formVal === 'close'){
        let exams = getCheckedRows('aTable');
        exams.map((exam) =>{
            let jsonData = { examGraded: exam}; 
            submitJsonData(
            'https://web.njit.edu/~tg253/490/examservice.php',
            'PUT',
             JSON.stringify(jsonData));
           }
        );
        console.log('close exams');
    }

}


//inserts columns into row 
function genColumn(item,row){
    var tdElement = document.createElement('td');
    tdElement.innerHTML = item;
    row.appendChild(tdElement);

}


//inserts rows into table 
function genQuestion(row,table){
    var tr = document.createElement('tr');
    table.appendChild(tr);
    var tdElement = document.createElement('td');
    tdElement.innerHTML = '<input type="checkbox">';
    tr.appendChild(tdElement);
    Object.values(row).forEach(value => {
        // add an input for score 
        genColumn(value,tr);
    });
}

//renders table 
async function renderQuestions(){
    const questionUrl = 'https://web.njit.edu/~asc8/cs490/beta/middle/question.php';
    let table = document.querySelector('#qTable');
    table.innerHTML = "";
    response = await  getJsonData(questionUrl);
    response.map((currentVal)=>{genQuestion(currentVal,table);});
}

//renders students                                                                                                                      
async function renderStudents(){
    const questionUrl = 'https://web.njit.edu/~tg253/490/userservice.php?role=student';
    let table = document.querySelector('#sTable');
    table.innerHTML = "";
    response = await  getJsonData(questionUrl);
    response.student.map((currentVal)=>{genQuestion(currentVal,table);});
}


// renders table of exams by professor 
async function renderExams(){
  const examUrl = "https://web.njit.edu/~asc8/cs490/beta/middle/exam.php";
  let table = document.querySelector('#aTable');
  table.innerHTML = "";
  let body = new Object; 
  body.professor = "snape"; 
  response = await  postJsonData(examUrl,body);
  console.log(response);
  response.exams.map((currentVal)=>{genQuestion(currentVal,table);});
}

//grade exam 

function gradeExam(event){
   event.preventDefault(); 
   console.log("graded"); 
}


//utility functions
async function getJsonData(url){
   let response = await fetch(url);
   return response.json();
  }

async function postJsonData(url,data){
   let response = await fetch(url,{
       method:'POST', 
       headers: {'Content-Type': 'application/json'},
       body: JSON.stringify(data)
   });
   return response.json();
  }

function submitJsonData(url, httpMethod, jsondata){
    fetch(url,{
        method: httpMethod,
        headers:{
            'Content-Type': 'application/json'
        },
        body : jsondata
        }).then((response) => response.json())
    return response;  
}

// insert question section 
//updates screen on question creation 
function updateScreen() {
        if (responseObject.questionInsertValid == "true") {
          insertSuccessText.innerText = "Question Insert Successful";
        } else {
          insertSuccessText.innerText = "Question Insert Unsuccessful";
        }
        renderQuestions();
}

//handle question submit 
function onSubmit(event) {
        event.preventDefault();
        let questionName = document.getElementById("name");
        let questionDescription = document.getElementById("description");
        let questionDifficulty = document.getElementById("difficulty");
        let questionCategory = document.getElementById("category");
        let testCaseInput1 = document.getElementById("testCaseInput1");
        let testCaseOutput1 = document.getElementById("testCaseOutput1");
        let testCaseInput2 = document.getElementById("testCaseInput2");
        let testCaseOutput2 = document.getElementById("testCaseOutput2");

        let json = {
          name: questionName.value,
          description: questionDescription.value,
          difficulty: questionDifficulty.value,
          category: questionCategory.value,
          testCases: [
            { input: testCaseInput1.value, output: testCaseOutput1.value },
            { input: testCaseInput2.value, output: testCaseOutput2.value }
          ]
        };

        var data = JSON.stringify(json);
        console.log(data);

        var request = new XMLHttpRequest();
        request.open("POST", "postQuestion.php", true);
        request.setRequestHeader("Content-type", "application/json");
        request.send(data);

        request.onreadystatechange = function() {
          if (request.status == 200 && request.readyState == 4) {
            responseObject = JSON.parse(request.responseText);
            updateScreen();
            console.log(responseObject);
          }
        };
       renderQuestions();
      }

// Adds function calls to html representation calls initial functions 
function init(){
    //use to validate user role 
    let user = sessionStorage.getItem('user');
    let role = 'professor';//sessionStorage.getItem('role');
    if (!(role === 'professor')){
        document.write('<h1>ACCESS DENIED</h1>');
    }
    document.getElementById('eForm').onsubmit = createExam;
    document.getElementById('qForm').onsubmit = onSubmit;
    document.getElementById('assignForm').onsubmit = assignExam;
    document.getElementById('gradeForm').onsubmit = gradeExam; 
    renderQuestions();
    renderExams();
    renderStudents();

}

// globals and init code 
var responseObject;

window.onload = init;
