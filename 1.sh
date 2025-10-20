git filter-branch --env-filter '

CORRECT_NAME="Michael Mariano"
CORRECT_EMAIL="dreamistop6@gmail.com"

    export GIT_AUTHOR_NAME="$CORRECT_NAME"
    export GIT_AUTHOR_EMAIL="$CORRECT_EMAIL"

' --tag-name-filter cat -- --branches --tags



git log --pretty=format:%ae | sort -u