/**
* Compiling new "test" by console command "gcc -std=c99 -o test test.c"
*/

#include <stdio.h>
#include <stdlib.h>
#include <errno.h>
#include <string.h>
#include <sys/types.h>
#include <sys/ipc.h>
#include <sys/msg.h>

/* Buffer struct for receiving messages */
struct php_buf {
    long code;/**< Код ошибки/сообщения*/
    long package; /**< ID упаковки/батча (зависит от кода ошибки)*/
    char desc[40];/**< Текстовое описание ошибки/сообщения */
    char comment[255];/**< Комментарий */
};

int main(void)
{
    struct php_buf buf;
    int msqid;
    key_t key;

    /* Generate key (/var/www/index.php must be accessible file) */
    if((key = ftok("test", 's')) == -1) {
        perror("ftok");
        exit(EXIT_FAILURE);
    }

    /* Create message queue */
    if((msqid = msgget(key, 0666 | IPC_CREAT)) == -1) {
        perror("msgget");
        exit(EXIT_FAILURE);
    }

    printf("Ready to get string from PHP!\n");

    /* Receive message */
    if(msgrcv(msqid, &buf, sizeof(buf), 0, 0) == -1) {
        perror("msgrcv");
        exit(EXIT_FAILURE);
    }

    /* Eliminate segmentation fault */
    buf.desc[39] = '\0';

    printf("Recieved from PHP: %ld\n", buf.code);
    printf("Recieved from PHP: %ld\n", buf.package);
    printf("Recieved from PHP: %s\n", buf.desc);
    printf("Recieved from PHP: %s\n", buf.comment);

    /* Destroy message queue */
    if(msgctl(msqid, IPC_RMID, NULL) == -1) {
        perror("msgctl");
        exit(EXIT_FAILURE);
    }
    return EXIT_SUCCESS;
}