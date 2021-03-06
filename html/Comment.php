<?php
require_once("headlessheader.php");

class Comments {

private $entityID;
private $db;

/*
    creates a new comment class with a unique entityID
    db is a instance of Database type
*/
public function __construct($entityID, $db)
{
    $this->entityID = $entityID;
    $this->db = $db;
}
/*
   adds a comment
*/
public function add($text)
{
    $this->db->interact("INSERT INTO Comment(UserID, Text, EntityID, UserName) VALUES(:user, :text, :entity, :userName)",
        array(":user" => $_SESSION["user_id"],
            ":text" => $text,
            ":entity" => $this->entityID,
            ":userName" => $_SESSION["user_name"]
        )
    );
}

/*
   List comments
*/
public function listComments()
{
    echo "<ul class='list-group'>";
    $this->db->each_row("SELECT * FROM Comment WHERE EntityID = :entity ORDER BY DateCreated ASC", array(":entity" => $this->entityID), function ($comment) {
        echo "<li class='list-group-item'>";
        echo "<div class='panel panel-default'>";
        echo "<div class='panel-heading'>";
        echo "Comment by " . $comment['UserName'] . " at " . $comment['DateCreated'] . ".";
        echo "</div>";
        echo "<div class='panel-body'>";
        echo htmlspecialchars($comment['Text']);
        echo "</div>";
        echo "</li>";
    });
    echo "</ul>";

}


/*
   javascript handling for adding a comment
*/
public function addCommentClientSide(){
?>
<div class='panel panel-default'>
    <div class='panel-heading'>Add Comment</div>
    <div class='panel-body'>
        <textarea class='form-control' rows='4' id='newCommentText'></textarea>
        <br>
        <button type='button' id="addCommentButton" class='btn btn-primary'>Add Comment</button>
    </div>
    <script>
        $("#addCommentButton").click(function () {
            if ($("#newCommentText").val() == '') {
                return;
            }
            $("#addCommentButton").attr('disabled', true);
            $.post("Comment.php", {
                    "newCommentText": $("#newCommentText").val(),
                    "entityID": "<?php echo $this->entityID ?>",
                },
                function (data) {
                    $("#commentList").html(data);
                    $("#newCommentText").val('');
                }
            );
            $("#addCommentButton").attr('disabled', false);
        });
    </script>
    <?php
    }

/*
   outputs the complete html/javascript for the comment system
*/
    public function show()
    {
        echo "<div class='panel panel-default'>";
        echo "<div class='panel-heading'><h4>Comments</h4></div>";
        echo "<div class='panel-body'>";
        echo "<span id='commentList'>";
        $this->listComments();
        echo "</span>";
        $this->addCommentClientSide();
        echo "</div>";
        return "";
    }
    }

    if (isset($_POST["newCommentText"])) {
        $comment = new Comments($_POST["entityID"], $db);
        $comment->add($_POST["newCommentText"]);
        $comment->listComments();
    }

    ?>
