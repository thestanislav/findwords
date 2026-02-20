import React, {useEffect, useMemo, useState, useRef} from "react";
import {Grid2 as Grid, Divider, Box, Button} from "@mui/material";
import {useDataProvider, useGetRecordId, useNotify, TextInput, FileInput, ImageField, SimpleForm} from "react-admin";
import {useFormContext} from "react-hook-form"
import ChatMessage, {ChatMsgProps} from "./ChatMessage";
import SendIcon from "@mui/icons-material/Send";

type TelegramAttachment = {
    file_id: string;
    file_unique_id?: string;
    file_name?: string;
    mime_type?: string;
    file_size?: number;
}

type TelegramAttachmentDocument = TelegramAttachment & {
    thumb?: TelegramAttachmentPhoto;
}

type TelegramAttachmentPhoto = TelegramAttachment & {
    width?: number;
    height?: number;
}

type TelegramAttachmentVideo = TelegramAttachmentPhoto & {
    duration?: number;
    thumb?: TelegramAttachmentPhoto;
}
type TelegramAttachmentVoice = TelegramAttachment & {
    duration?: number;
}

type TelegramAttachmentAudio = TelegramAttachment & {
    duration?: number;
}
export type TelegramMessageObject = {
    audio?: TelegramAttachmentAudio;
    document?: TelegramAttachmentPhoto;
    animation?: Animation;
    photo?: TelegramAttachmentPhoto[];
    video?: TelegramAttachmentVideo;
    voice?: TelegramAttachmentVoice;
    caption?: string;
}

type MessageInfoType = {
    message: string,
    ctime: string,
    owner: string,
    avatar: string,
    isModerator: boolean,
    object?: TelegramMessageObject
}

type MessageFormData = {
    messageText: string;
    attachment?: {
        rawFile?: File;
        src?: string;
        title?: string;
    } | null;
}

const MessageFormContent = ({ resetRef, submitting }: { resetRef: React.MutableRefObject<(() => void) | null>, submitting: boolean }) => {
    const { watch, reset } = useFormContext();
    const messageText = watch('messageText');
    
    // Store reset function in ref
    React.useEffect(() => {
        resetRef.current = reset;
    }, [resetRef, reset]);
    
    return (
        <Grid container spacing={2} fullWidth>
            <Grid size={10}>
                <Box sx={{ display: 'flex', flexDirection: 'column', gap: 2 }}>
                    <TextInput 
                        source="messageText"
                        label="Введите сообщение"
                        multiline
                        fullWidth
                        validate={(value) => value && value.length >= 2 ? undefined : 'Минимум 2 символа'}
                    />
                    <FileInput 
                        source="attachment" 
                        label="Прикрепить файл"
                        accept="image/*,video/*,audio/*,.pdf,.doc,.docx"
                        maxSize={10000000}
                    >
                        <ImageField source="src" title="title" />
                    </FileInput>
                </Box>
            </Grid>
            <Grid
                size={2}
                sx={{
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center'
                }}
            >
                <Button 
                    type="submit"
                    disabled={!messageText || messageText.length < 2 || submitting}
                    variant="contained"
                >
                    <SendIcon color="inherit"/>
                </Button>
            </Grid>
        </Grid>
    );
}

export const NutgramConversation = () => {

    const userId = useGetRecordId();
    const [submitting, setSubmitting] = useState<boolean>(false)
    const [refreshFactor, setRefreshFactor] = useState<number>(0)
    const [conversation, setConversation] = useState<MessageInfoType[]>()
    const resetRef = useRef<(() => void) | null>(null)
    const dataProvider = useDataProvider();
    const notify = useNotify();

    useEffect(() => {
        dataProvider.fetch('expras-nutgram-bot/conversation', {
            method: 'post',
            body: JSON.stringify({
                userId,
            }),
            headers: {
                'Content-Type': 'application/json'
            }
        })
            .then(r => r.json())
            .then(setConversation)
    }, [refreshFactor]);

    const conversationMessages = useMemo(() => (conversation || []).reduce(
        (accumulator, {message, ctime, owner, isModerator, avatar, object}) => {

            const side = (isModerator ? 'right' : 'left');

            const lastMessages = (accumulator.length === 0 || accumulator[accumulator.length - 1].side !== side) ? {
                side,
                avatar,
                initials: owner,
                messages: [],
                style: {},
                className: ''
            } as ChatMsgProps : accumulator.pop()


            lastMessages.messages.push({
                date: new Date(ctime),
                text: message,
                object
            })

            return accumulator.concat([lastMessages]);
        }, [] as ChatMsgProps[]), [conversation])

    const handleSendMessage = (data: MessageFormData) => {
        setSubmitting(true);
        
        const formData = new FormData();
        formData.append('userId', userId?.toString() || '');
        formData.append('messageText', data.messageText);
        
        // Add file attachment if present
        if (data.attachment?.rawFile) {
            formData.append('attachment', data.attachment.rawFile);
        }
        
        dataProvider.fetch('expras-nutgram-bot/send-message', {
            method: 'post',
            body: formData
        })
            .then(r => r.json())
            .then(({success, message}) => {
                if (success) {
                    notify(`Сообщение успешно отправлено`, {type: 'success'});
                    setRefreshFactor(prev => prev + 1);
                    // Reset form after successful send
                    if (resetRef.current) {
                        resetRef.current();
                    }
                } else {
                    notify(message || 'Произошла неизвестная ошибка', {type: 'error'});
                }
                setSubmitting(false);
            })
            .catch((error) => {
                notify('Ошибка при отправке сообщения', {type: 'error'});
                setSubmitting(false);
            });
    }

    return <div>
        <SimpleForm 
            onSubmit={handleSendMessage}
            defaultValues={{
                messageText: '',
                attachment: null
            }}
            toolbar={false}
        >
            <MessageFormContent resetRef={resetRef} submitting={submitting} />
        </SimpleForm>

        <Divider sx={{ my: 2 }}/>
        <Box sx={{ pt: 2 }}>
            {conversationMessages.map((props, i) => <ChatMessage key={i} {...props} />)}
        </Box>
    </div>

}

export default NutgramConversation;